<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = [
            [
                'origin_city' => 'Palu',
                'destination_city' => 'Makassar',
                'distance_km' => 240,
                'estimated_duration_minutes' => 360, // 6 jam
                'base_price' => 150000,
            ],
            [
                'origin_city' => 'Palu',
                'destination_city' => 'Donggala',
                'distance_km' => 25,
                'estimated_duration_minutes' => 45, // 45 menit
                'base_price' => 50000,
            ],
            [
                'origin_city' => 'Palu',
                'destination_city' => 'Ampana',
                'distance_km' => 120,
                'estimated_duration_minutes' => 180, // 3 jam
                'base_price' => 100000,
            ],
            [
                'origin_city' => 'Makassar',
                'destination_city' => 'Gowa',
                'distance_km' => 30,
                'estimated_duration_minutes' => 60, // 1 jam
                'base_price' => 60000,
            ],
            [
                'origin_city' => 'Makassar',
                'destination_city' => 'Sungguminasa',
                'distance_km' => 45,
                'estimated_duration_minutes' => 90, // 1.5 jam
                'base_price' => 75000,
            ],
            [
                'origin_city' => 'Gowa',
                'destination_city' => 'Sungguminasa',
                'distance_km' => 20,
                'estimated_duration_minutes' => 40, // 40 menit
                'base_price' => 45000,
            ],
            [
                'origin_city' => 'Donggala',
                'destination_city' => 'Ampana',
                'distance_km' => 150,
                'estimated_duration_minutes' => 240, // 4 jam
                'base_price' => 120000,
            ],
        ];

        foreach ($routes as $route) {
            Route::create([
                ...$route,
                'is_active' => true,
            ]);
        }
    }
}
