<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\Wallet;

class WalletHelper
{
    /**
     * Dapatkan wallet user saat ini (Single Company Mode dalam Tenant)
     */
    public static function getWallet()
    {
        // 1. CEK KONTEKS: Pastikan hanya dieksekusi di dalam Tenant, bukan di Central Panel
        if (! function_exists('tenant') || ! tenant()) {
            return null;
        }

        $user = auth()->user();

        // Pastikan ada user yang sedang login
        if (! $user) {
            return null;
        }

        // Karena single company di dalam tenant, ambil company pertama
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
