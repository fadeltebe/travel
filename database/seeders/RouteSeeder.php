<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $palu   = Agent::where('code', 'PALU01')->first();
        $poso   = Agent::where('code', 'POSO01')->first();
        $ampana = Agent::where('code', 'AMPANA01')->first();

        // Bi-directional routes between the 3 agents
        $routes = [
            // Palu <-> Poso
            ['origin_agent_id' => $palu->id, 'destination_agent_id' => $poso->id, 'distance_km' => 210, 'estimated_duration_minutes' => 270, 'base_price' => 85000, 'is_active' => true],
            ['origin_agent_id' => $poso->id, 'destination_agent_id' => $palu->id, 'distance_km' => 210, 'estimated_duration_minutes' => 270, 'base_price' => 85000, 'is_active' => true],
            
            // Poso <-> Ampana
            ['origin_agent_id' => $poso->id, 'destination_agent_id' => $ampana->id, 'distance_km' => 160, 'estimated_duration_minutes' => 240, 'base_price' => 75000, 'is_active' => true],
            ['origin_agent_id' => $ampana->id, 'destination_agent_id' => $poso->id, 'distance_km' => 160, 'estimated_duration_minutes' => 240, 'base_price' => 75000, 'is_active' => true],

            // Palu <-> Ampana
            ['origin_agent_id' => $palu->id, 'destination_agent_id' => $ampana->id, 'distance_km' => 370, 'estimated_duration_minutes' => 480, 'base_price' => 150000, 'is_active' => true],
            ['origin_agent_id' => $ampana->id, 'destination_agent_id' => $palu->id, 'distance_km' => 370, 'estimated_duration_minutes' => 480, 'base_price' => 150000, 'is_active' => true],
        ];

        foreach ($routes as $route) {
            Route::create($route);
        }
    }
}
