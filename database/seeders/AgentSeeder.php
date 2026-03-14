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
                'email'           => 'palu@sultengexpress.com',
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
                'email'           => 'poso@sultengexpress.com',
                'commission_rate' => 5.00,
                'is_active'       => true,
            ],
            [
                'name'            => 'Agen Luwuk',
                'slug'            => 'agen-luwuk',
                'code'            => 'LUWUK01',
                'city'            => 'Luwuk',
                'address'         => 'Jl. Ahmad Yani No. 15, Luwuk',
                'phone'           => '085243456789',
                'email'           => 'luwuk@sultengexpress.com',
                'commission_rate' => 5.00,
                'is_active'       => true,
            ],
            [
                'name'            => 'Agen Toli-Toli',
                'slug'            => 'agen-toli-toli',
                'code'            => 'TOLI01',
                'city'            => 'Toli-Toli',
                'address'         => 'Jl. Sudirman No. 8, Toli-Toli',
                'phone'           => '085244567890',
                'email'           => 'tolitoli@sultengexpress.com',
                'commission_rate' => 5.00,
                'is_active'       => true,
            ],
            [
                'name'            => 'Agen Morowali',
                'slug'            => 'agen-morowali',
                'code'            => 'MORO01',
                'city'            => 'Morowali',
                'address'         => 'Jl. Trans Sulawesi No. 3, Morowali',
                'phone'           => '085245678901',
                'email'           => 'morowali@sultengexpress.com',
                'commission_rate' => 5.00,
                'is_active'       => true,
            ],
        ];

        foreach ($agents as $agent) {
            Agent::create($agent);
        }
    }
}
