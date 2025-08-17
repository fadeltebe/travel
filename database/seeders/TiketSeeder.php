<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tiket;
use App\Models\Pemesanan;
use App\Models\Penumpang;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TiketSeeder extends Seeder
{
    public function run(): void
    {
        $pemesananList = Pemesanan::all();

        foreach ($pemesananList as $p) {
            // harga per tiket (bagi rata)
            $hargaPerTiket = $p->jumlah_penumpang ? ($p->total_harga / $p->jumlah_penumpang) : $p->total_harga;

            for ($i = 0; $i < $p->jumlah_penumpang; $i++) {
                // buat penumpang baru untuk setiap tiket (agar relasi jelas)
                $penumpang = Penumpang::create([
                    'nama' => 'Penumpang ' . strtoupper(substr(Str::random(6), 0, 6)),
                    'nik' => null,
                    'jenis_kelamin' => (rand(0, 1) ? 'L' : 'P'),
                    'tanggal_lahir' => now()->subYears(rand(18, 50))->toDateString(),
                    'telepon' => '0812' . rand(10000000, 99999999),
                    'email' => null,
                    'alamat' => 'Alamat contoh',
                ]);

                // hitung nomor kursi selanjutnya untuk jadwal yang sama
                $usedSeats = DB::table('tikets')
                    ->join('pemesanans', 'tikets.pemesanan_id', '=', 'pemesanans.id')
                    ->where('pemesanans.jadwal_id', $p->jadwal_id)
                    ->count();

                $seatNumber = $usedSeats + 1;

                Tiket::create([
                    'agen_id' => $p->agen_id,
                    'pemesanan_id' => $p->id,
                    'penumpang_id' => $penumpang->id,
                    'nomor_tiket' => 'TKT-' . strtoupper(Str::random(10)),
                    'nomor_kursi' => $seatNumber,
                    'harga' => $hargaPerTiket,
                    'status' => 'active',
                    'check_in_at' => null,
                ]);
            }
        }
    }
}
