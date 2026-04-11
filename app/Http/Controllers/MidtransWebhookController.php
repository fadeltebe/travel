<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topup;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Memproses Notifikasi Pembayaran / Webhook dari Midtrans
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        // 1. Dapatkan parameter untuk verifikasi HMAC SHA512
        $serverKey = config('services.midtrans.server_key');
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $paymentType = $payload['payment_type'] ?? 'midtrans_snap';

        // 2. Kalkulasi Ulang Key untuk mencegah pemalsuan Request
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($expectedSignature !== $signatureKey) {
            Log::warning('Midtrans Webhook: Invalid Signature', [
                'expected' => $expectedSignature,
                'received' => $signatureKey,
                'order' => $orderId
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 3. Temukan data Tagihan (Topup Invoice)
        $topup = Topup::where('invoice_number', $orderId)->first();

        if (!$topup) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Jika invoice sudah Lunas, hiraukan webhook untuk mencegah saldo masuk berlapis (Idempotent)
        if ($topup->status === 'paid') {
            return response()->json(['message' => 'Already updated to paid'], 200);
        }

        // 4. Proses Eksekusi Berdasarkan Status Pembayaran Midtrans
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {

            // Bungkus dalam Database Transaction agar aman jika ada crash di tengah eksekusi
            DB::transaction(function () use ($topup, $paymentType) {
                // Tandai Topup Lunas
                $topup->update([
                    'status' => 'paid',
                    'payment_method' => $paymentType,
                    'paid_at' => now(),
                ]);

                // Cari dompetnya
                $walletQuery = Wallet::where('company_id', $topup->company_id);
                if ($topup->agent_id) {
                    $walletQuery->where('agent_id', $topup->agent_id);
                } else {
                    $walletQuery->whereNull('agent_id');
                }

                $wallet = $walletQuery->first();

                // Suntikkan Saldo!
                if ($wallet) {
                    $wallet->balance += $topup->amount;
                    $wallet->save();

                    // Buat Log Dompet/Mutasi Rekening Sistem
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'credit',
                        'amount' => $topup->amount,
                        'balance_after' => $wallet->balance,
                        'description' => "Isi Saldo Token\n(Midtrans: {$topup->invoice_number})",
                        'reference_id' => $topup->id,
                        'reference_type' => Topup::class,
                    ]);
                }
            });

            Log::info("Payment Success & Balance Added: {$topup->invoice_number}");
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            // Batalkan Tagihan
            $topup->update([
                'status' => 'failed',
            ]);
            Log::info("Payment Failed/Expired: {$topup->invoice_number}");
        }

        return response()->json(['message' => 'Webhook received and processed'], 200);
    }
}
