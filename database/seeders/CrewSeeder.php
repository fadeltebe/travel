<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Crew;
use Carbon\Carbon;

class CrewSeeder extends Seeder
{
    public function run(): void
    {
        $crews = [
            [
                'nama' => 'Rudi Hartono',
                'no_hp' => '081200000001',
                'alamat' => 'Jl. Mawar No.1',
                'nik' => '7202010101010001',
                'tempat_lahir' => 'Palu',
                'tanggal_lahir' => Carbon::parse('1990-02-02'),
                'jenis_kelamin' => 'L',
            ],
            [
                'nama' => 'Fajar Ramadhan',
                'no_hp' => '081200000002',
                'alamat' => 'Jl. Akasia No.2',
                'nik' => '7202020202020002',
                'tempat_lahir' => 'Ampana',
                'tanggal_lahir' => Carbon::parse('1991-04-04'),
                'jenis_kelamin' => 'L',
            ],
            [
                'nama' => 'Eko Prasetyo',
                'no_hp' => '081200000003',
                'alamat' => 'Jl. Flamboyan No.3',
                'nik' => '7202030303030003',
                'tempat_lahir' => 'Luwuk',
                'tanggal_lahir' => Carbon::parse('1989-06-06'),
                'jenis_kelamin' => 'L',
            ],
            [
                'nama' => 'Imam Saputra',
                'no_hp' => '081200000004',
                'alamat' => 'Jl. Kenanga No.4',
                'nik' => '7202040404040004',
                'tempat_lahir' => 'Palu',
                'tanggal_lahir' => Carbon::parse('1986-08-08'),
                'jenis_kelamin' => 'L',
            ],
            [
                'nama' => 'Maya Sari',
                'no_hp' => '081200000005',
                'alamat' => 'Jl. Melur No.5',
                'nik' => '7202050505050005',
                'tempat_lahir' => 'Ampana',
                'tanggal_lahir' => Carbon::parse('1993-12-12'),
                'jenis_kelamin' => 'P',
            ],
            [
                'nama' => 'Dewi Lestari',
                'no_hp' => '081200000006',
                'alamat' => 'Jl. Cempaka No.6',
                'nik' => '7202060606060006',
                'tempat_lahir' => 'Luwuk',
                'tanggal_lahir' => Carbon::parse('1995-10-10'),
                'jenis_kelamin' => 'P',
            ],
        ];

        foreach ($crews as $c) {
            Crew::create($c);
        }
    }
}
