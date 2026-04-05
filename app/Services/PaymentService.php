<?php

namespace App\Services;

use App\Models\Topup;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Membuat Snap Token Midtrans untuk Invoice Top-Up
     *
     * @param Topup $topup
     * @return string|null
     */
    public function createSnapToken(Topup $topup)
    {
        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production');

        $baseUrl = $isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions' 
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        // Detail pelanggan (opsional tapi disarankan oleh Midtrans)
        $user = $topup->agent ? $topup->agent->user : auth()->user();
        
        $customerDetails = [
            'first_name' => $user->name ?? 'Admin',
            'email' => $user->email ?? 'admin@travel.com',
        ];

        // Format data yang dikirim ke Midtrans
        $payload = [
            'transaction_details' => [
                'order_id'     => $topup->invoice_number, // Harus unik
                'gross_amount' => (int) $topup->amount,
            ],
            'customer_details' => $customerDetails,
            'item_details' => [
                [
                    'id' => 'TOPUP-TOKEN',
                    'price' => (int) $topup->amount,
                    'quantity' => 1,
                    'name' => 'Top-Up Saldo Token Travel',
                ]
            ]
        ];

        try {
            // Midtrans menggunakan Basic Auth dengan ServerKey:
            // Kosongkan bagian 'password' karena Midtrans hanya butuh ServerKey di username.
            $response = Http::withBasicAuth($serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($baseUrl, $payload);

            if ($response->successful()) {
                return $response->json('token');
            }

            Log::error('Midtrans Snap Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Midtrans Exception: ' . $e->getMessage());
            return null;
        }
    }
}
