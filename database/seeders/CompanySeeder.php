<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'Megan Jaya',
            'code' => 'MJ-001',
            'email' => 'megan@jaya.com',
            'phone' => '0451-123456',
            'address' => 'Jl. Trans Sulawesi No. 123, Palu, Sulawesi Tengah',
            'logo' => null,
            'npwp' => '12.345.678.9-012.345',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
