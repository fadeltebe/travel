<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class TokenService
{
    /**
     * 1. Mencari Dompet yang Tepat (Sesuai Pengaturan Bos)
     */
    public function getWallet(?int $companyId, ?int $agentId, bool $lock = false)
    {
        // Single company mode - auto-detect company jika null
        if (!$companyId) {
            $company = Company::first();
            $companyId = $company->id ?? 1;
        }

        $company = Company::findOrFail($companyId);
        $query = Wallet::where('company_id', $companyId);

        // Jika mode tagihan dipusatkan ke bos, cari dompet tanpa agent_id
        if ($company->billing_mode === 'centralized') {
            $query->whereNull('agent_id');
        } else {
            // Jika mode agen mandiri, cari dompet milik agen tersebut
            if (!$agentId) {
                $user = auth()->user();
                $agentId = $user->agent_id;
            }
            $query->where('agent_id', $agentId);
        }

        // KUNCI DATABASE (Pessimistic Locking) agar tidak terjadi saldo minus
        if ($lock) {
            $query->lockForUpdate();
        }

        $wallet = $query->first();

        // Jika dompet belum ada sama sekali, buatkan otomatis dengan saldo Rp 0
        if (!$wallet) {
            $wallet = Wallet::create([
                'company_id' => $companyId,
                'agent_id'   => $company->billing_mode === 'centralized' ? null : $agentId,
                'balance'    => 0,
            ]);
        }

        return $wallet;
    }

    /**
     * 2. Pengecekan Saldo (Dipakai di UI untuk mematikan tombol)
     */
    public function hasEnoughBalance(?int $companyId, ?int $agentId, float $amount): bool
    {
        try {
            $wallet = $this->getWallet($companyId, $agentId);
            return $wallet->balance >= $amount;
        } catch (Exception $e) {
            return false; // Jika ada error, anggap saldo tidak cukup
        }
    }

    /**
     * 3. Pemotongan Saldo (Debit) saat simpan Penumpang/Kargo
     */
    public function deduct(?int $companyId, ?int $agentId, float $amount, string $description, $reference = null)
    {
        return DB::transaction(function () use ($companyId, $agentId, $amount, $description, $reference) {
            // Panggil dompet dan KUNCI baris databasenya
            $wallet = $this->getWallet($companyId, $agentId, true);

            // Cek saldo terakhir
            if ($wallet->balance < $amount) {
                throw new Exception('Gagal! Saldo token tidak mencukupi. Silakan lakukan Top-Up terlebih dahulu.');
            }

            // Kurangi saldo
            $wallet->balance -= $amount;
            $wallet->save();

            // Catat ke Buku Mutasi (Biar bos tidak marah tanya uangnya ke mana)
            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'debit',
                'amount'         => $amount,
                'balance_after'  => $wallet->balance,
                'description'    => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id'   => $reference ? $reference->id : null,
            ]);

            return $wallet;
        });
    }

    /**
     * 4. Penambahan Saldo (Credit) dipakai oleh Webhook Midtrans nanti
     */
    public function credit(int $walletId, float $amount, string $description, $reference = null)
    {
        return DB::transaction(function () use ($walletId, $amount, $description, $reference) {
            // Kunci dompet saat mengisi saldo
            $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

            // Tambah saldo
            $wallet->balance += $amount;
            $wallet->save();

            // Catat ke Buku Mutasi
            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'credit',
                'amount'         => $amount,
                'balance_after'  => $wallet->balance,
                'description'    => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id'   => $reference ? $reference->id : null,
            ]);

            return $wallet;
        });
    }
}
