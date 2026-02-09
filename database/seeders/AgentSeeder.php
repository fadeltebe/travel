<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Agents untuk Company 1 (Harapan Jaya)
        Agent::create([
            'name' => 'Agen Palu',
            'slug' => 'agen-palu',
            'code' => 'PALU01',
            'city' => 'Palu',
            'address' => 'Jl. Ahmad Yani No. 123, Palu',
            'phone' => '0451-123456',
            'email' => 'palu@harapanjaya.com',
            'commission_rate' => 5.00,
            'is_active' => true,
        ]);

        Agent::create([
            'name' => 'Agen Donggala',
            'slug' => 'agen-donggala',
            'code' => 'DONGGALA01',
            'city' => 'Donggala',
            'address' => 'Jl. Merdeka No. 456, Donggala',
            'phone' => '0456-654321',
            'email' => 'donggala@harapanjaya.com',
            'commission_rate' => 5.00,
            'is_active' => true,
        ]);

        Agent::create([
            'name' => 'Agen Ampana',
            'slug' => 'agen-ampana',
            'code' => 'AMPANA01',
            'city' => 'Ampana',
            'address' => 'Jl. Jenderal Sudirman No. 789, Ampana',
            'phone' => '0460-789012',
            'email' => 'ampana@harapanjaya.com',
            'commission_rate' => 5.00,
            'is_active' => true,
        ]);

        // Agents untuk Company 2 (Lancar Jaya)
        Agent::create([
            'name' => 'Agen Makassar',
            'slug' => 'agen-makassar',
            'code' => 'MKS01',
            'city' => 'Makassar',
            'address' => 'Jl. Gatot Subroto No. 456, Makassar',
            'phone' => '0411-987654',
            'email' => 'makassar@lancarjaya.com',
            'commission_rate' => 5.00,
            'is_active' => true,
        ]);

        Agent::create([
            'name' => 'Agen Gowa',
            'slug' => 'agen-gowa',
            'code' => 'GOWA01',
            'city' => 'Gowa',
            'address' => 'Jl. Pendidikan No. 321, Gowa',
            'phone' => '0411-321098',
            'email' => 'gowa@lancarjaya.com',
            'commission_rate' => 5.00,
            'is_active' => true,
        ]);

        Agent::create([
            'name' => 'Agen Sungguminasa',
            'slug' => 'agen-sungguminasa',
            'code' => 'SUNGGU01',
            'city' => 'Sungguminasa',
            'address' => 'Jl. Jenderal Ahmad Yani No. 654, Sungguminasa',
            'phone' => '0411-654321',
            'email' => 'sungguminasa@lancarjaya.com',
            'commission_rate' => 5.00,
            'is_active' => true,
        ]);
    }
}
