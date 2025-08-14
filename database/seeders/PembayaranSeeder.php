<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $metode = ['cash', 'transfer', 'e_wallet', 'credit_card'];
        $statuses = ['pending', 'success', 'failed', 'cancelled'];

        foreach (Pemesanan::all() as $p) {
            // decide apakah bayar penuh/partial atau belum dibayar
            // jika pemesanan status == 'paid' buat success, kalau cancelled buat cancelled, otherwise random
            if ($p->status === 'paid') {
                $status = 'success';
                $tanggalBayar = Carbon::now()->subMinutes(rand(10, 1000));
                $jumlah = $p->total_harga;
            } elseif ($p->status === 'cancelled') {
                $status = 'cancelled';
                $tanggalBayar = null;
                $jumlah = 0;
            } else {
                // sebagian berupa pending/success random
                $status = $statuses[array_rand($statuses)];
                $tanggalBayar = $status === 'success' ? Carbon::now()->subMinutes(rand(10, 1000)) : null;
                $jumlah = $status === 'success' ? $p->total_harga : 0;
            }

            Pembayaran::create([
                'agen_id' => $p->agen_id,
                'pemesanan_id' => $p->id,
                'kode_pembayaran' => 'PAY-' . strtoupper(Str::random(10)),
                'jumlah' => $jumlah,
                'metode_pembayaran' => $metode[array_rand($metode)],
                'status' => $status,
                'tanggal_pembayaran' => $tanggalBayar,
                'referensi_eksternal' => null,
                'catatan' => null,
            ]);
        }
    }
}
