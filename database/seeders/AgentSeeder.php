<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            [
                'name'            => 'Agen Palu',
                'slug'            => 'agen-palu',
                'code'            => 'PALU01',
                'city'            => 'Palu',
                'address'         => 'Jl. Diponegoro No. 10, Palu',
                'phone'           => '085241234567',
                'email'           => 'palu@sultengtravel.com',
                'commission_rate' => 5.00,
                'is_active'       => true,
            ],
            [
                'name'            => 'Agen Poso',
                'slug'            => 'agen-poso',
                'code'            => 'POSO01',
                'city'            => 'Poso',
                'address'         => 'Jl. Pulau Sumatera No. 5, Poso',
                'phone'           => '085242345678',
                'email'           => 'poso@sultengtravel.com',
                'commission_rate' => 5.00,
                'is_active'       => true,
            ],
            [
                'name'            => 'Agen Ampana',
                'slug'            => 'agen-ampana',
                'code'            => 'AMPANA01',
                'city'            => 'Ampana',
                'address'         => 'Jl. Trans Sulawesi, Ampana',
                'phone'           => '085243456789',
                'email'           => 'ampana@sultengtravel.com',
                'commission_rate' => 5.00,
                'is_active'       => true,
            ],
        ];

        foreach ($agents as $agent) {
            Agent::create($agent);
        }
    }
}
