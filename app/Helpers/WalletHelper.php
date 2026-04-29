<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\Wallet;

class WalletHelper
{
    /**
     * Dapatkan wallet user saat ini (Single Company Mode)
     */
    public static function getWallet()
    {
        $user = auth()->user();

        // Karena single company, ambil company pertama
        $company = Company::first();

        if (! $company) {
            // Tenant database belum berisi perusahaan; jangan buat dompet tanpa company_id yang valid.
            return null;
        }

        $query = Wallet::query();

        // Cek siapa yang bayar berdasarkan mode tagihan
        if ($company->billing_mode === 'per_agent') {
            // Jika agen mandiri, cari dompet milik agen tersebut
            $query->where('agent_id', $user->agent_id);
        } else {
            // Jika sentralisasi (Bos), cari Dompet Utama (agent_id kosong)
            $query->whereNull('agent_id');
        }

        $wallet = $query->first();

        // Auto-create jika dompet belum ada
        if (! $wallet) {
            $wallet = Wallet::create([
                'company_id' => $company->id,
                'agent_id' => $company->billing_mode === 'per_agent' ? $user->agent_id : null,
                'balance'  => 0,
            ]);
        }

        return $wallet;
    }

    /**
     * Format balance ke Rupiah
     */
    public static function formatBalance($amount = null)
    {
        if ($amount === null) {
            $wallet = static::getWallet();
            $amount = $wallet?->balance ?? 0;
        }

        return 'Rp' . number_format($amount, 0, ',', '.');
    }

    /**
     * Check apakah balance cukup
     */
    public static function hasEnoughBalance($requiredAmount)
    {
        $wallet = static::getWallet();
        return ($wallet?->balance ?? 0) >= $requiredAmount;
    }
}
