<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $palu    = Agent::where('code', 'PALU01')->first();
        $poso    = Agent::where('code', 'POSO01')->first();
        $luwuk   = Agent::where('code', 'LUWUK01')->first();
        $toli    = Agent::where('code', 'TOLI01')->first();
        $morowali = Agent::where('code', 'MORO01')->first();

        $routes = [
            [
                'origin_agent_id'            => $palu->id,
                'destination_agent_id'        => $poso->id,
                'distance_km'                => 210,
                'estimated_duration_minutes' => 270,
                'base_price'                 => 85000,
                'is_active'                  => true,
            ],
            [
                'origin_agent_id'            => $poso->id,
                'destination_agent_id'        => $palu->id,
                'distance_km'                => 210,
                'estimated_duration_minutes' => 270,
                'base_price'                 => 85000,
                'is_active'                  => true,
            ],
            [
                'origin_agent_id'            => $palu->id,
                'destination_agent_id'        => $luwuk->id,
                'distance_km'                => 520,
                'estimated_duration_minutes' => 600,
                'base_price'                 => 185000,
                'is_active'                  => true,
            ],
            [
                'origin_agent_id'            => $palu->id,
                'destination_agent_id'        => $toli->id,
                'distance_km'                => 290,
                'estimated_duration_minutes' => 360,
                'base_price'                 => 110000,
                'is_active'                  => true,
            ],
            [
                'origin_agent_id'            => $poso->id,
                'destination_agent_id'        => $morowali->id,
                'distance_km'                => 180,
                'estimated_duration_minutes' => 240,
                'base_price'                 => 75000,
                'is_active'                  => true,
            ],
        ];

        foreach ($routes as $route) {
            Route::create($route);
        }
    }
}
