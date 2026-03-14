<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $buses   = Bus::all();
        $routes  = Route::all();
        $driver  = User::where('role', Role::Driver)->first();

        $schedules = [
            [
                'route_id'        => $routes[0]->id, // Palu → Poso
                'bus_id'          => $buses[0]->id,
                'driver_id'       => $driver->id,
                'departure_date'  => now()->format('Y-m-d'),
                'departure_time'  => '08:00',
                'arrival_date'    => now()->format('Y-m-d'),
                'arrival_time'    => '12:30',
                'price'           => 85000,
                'available_seats' => $buses[0]->total_seats,
                'status'          => 'scheduled',
            ],
            [
                'route_id'        => $routes[0]->id, // Palu → Poso
                'bus_id'          => $buses[1]->id,
                'driver_id'       => $driver->id,
                'departure_date'  => now()->format('Y-m-d'),
                'departure_time'  => '14:00',
                'arrival_date'    => now()->format('Y-m-d'),
                'arrival_time'    => '18:30',
                'price'           => 85000,
                'available_seats' => $buses[1]->total_seats,
                'status'          => 'scheduled',
            ],
            [
                'route_id'        => $routes[1]->id, // Poso → Palu
                'bus_id'          => $buses[0]->id,
                'driver_id'       => $driver->id,
                'departure_date'  => now()->addDay()->format('Y-m-d'),
                'departure_time'  => '07:00',
                'arrival_date'    => now()->addDay()->format('Y-m-d'),
                'arrival_time'    => '11:30',
                'price'           => 85000,
                'available_seats' => $buses[0]->total_seats,
                'status'          => 'scheduled',
            ],
            [
                'route_id'        => $routes[2]->id, // Palu → Luwuk
                'bus_id'          => $buses[2]->id,
                'driver_id'       => $driver->id,
                'departure_date'  => now()->format('Y-m-d'),
                'departure_time'  => '06:00',
                'arrival_date'    => now()->format('Y-m-d'),
                'arrival_time'    => '16:00',
                'price'           => 185000,
                'available_seats' => $buses[2]->total_seats,
                'status'          => 'scheduled',
            ],
            [
                'route_id'        => $routes[3]->id, // Palu → Toli-Toli
                'bus_id'          => $buses[1]->id,
                'driver_id'       => $driver->id,
                'departure_date'  => now()->addDays(2)->format('Y-m-d'),
                'departure_time'  => '09:00',
                'arrival_date'    => now()->addDays(2)->format('Y-m-d'),
                'arrival_time'    => '15:00',
                'price'           => 110000,
                'available_seats' => $buses[1]->total_seats,
                'status'          => 'scheduled',
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
