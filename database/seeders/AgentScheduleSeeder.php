<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agent;
use App\Models\Route;
use App\Models\Bus;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Cargo;
use App\Models\Passenger;
use App\Models\BusLayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgentScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Setup Bus Layout, Bus, Driver, User
            $busLayout = BusLayout::firstOrCreate(
                ['name' => 'Layout Standard 40 Seat'],
                ['total_rows' => 10, 'total_columns' => 4, 'total_seats' => 40]
            );

            $bus = Bus::firstOrCreate(
                ['plate_number' => 'DN 9999 XX'],
                ['name' => 'Bus Dummy Seeder', 'type' => 'Big Bus', 'bus_layout_id' => $busLayout->id, 'total_seats' => $busLayout->total_seats]
            );

            $driver = User::firstOrCreate(
                ['email' => 'driver_seeder@test.com'],
                ['name' => 'Driver Seeder', 'password' => bcrypt('password')]
            );

            $bookerUser = User::firstOrCreate(
                ['email' => 'booker_seeder@test.com'],
                ['name' => 'Booker Seeder', 'password' => bcrypt('password')]
            );

            // 2. Setup Agents
            $agentsData = [
                ['name' => 'Agen Palu', 'slug' => 'agen-palu', 'code' => 'PALU99', 'city' => 'Palu'],
                ['name' => 'Agen Poso', 'slug' => 'agen-poso', 'code' => 'POSO99', 'city' => 'Poso'],
                ['name' => 'Agen Ampana', 'slug' => 'agen-ampana', 'code' => 'AMP99', 'city' => 'Ampana'],
            ];

            $agents = [];
            foreach ($agentsData as $data) {
                // Gunakan slug mencegah Duplicate Entry
                $agents[$data['city']] = Agent::firstOrCreate(['slug' => $data['slug']], $data);
            }

            // 3. Setup Routes
            $routes = [
                'Palu' => Route::firstOrCreate(['origin_agent_id' => $agents['Palu']->id, 'destination_agent_id' => $agents['Poso']->id], ['base_price' => 150000]),
                'Poso' => Route::firstOrCreate(['origin_agent_id' => $agents['Poso']->id, 'destination_agent_id' => $agents['Ampana']->id], ['base_price' => 120000]),
                'Ampana' => Route::firstOrCreate(['origin_agent_id' => $agents['Ampana']->id, 'destination_agent_id' => $agents['Palu']->id], ['base_price' => 180000]),
            ];

            // 4. Create Schedules, Bookings, Cargos, Passengers
            $startDate = Carbon::tomorrow();
            
            foreach ($agents as $city => $agent) {
                $route = $routes[$city];

                for ($day = 0; $day < 3; $day++) {
                    $scheduleDate = $startDate->copy()->addDays($day);

                    // Create Schedule
                    $schedule = Schedule::create([
                        'route_id' => $route->id,
                        'bus_id' => $bus->id,
                        'driver_id' => $driver->id,
                        'departure_date' => $scheduleDate->format('Y-m-d'),
                        'departure_time' => '10:00:00',
                        'price' => $route->base_price,
                        'available_seats' => $bus->total_seats - 10, // 10 passengers
                        'status' => 'scheduled'
                    ]);

                    // Create Booking for this schedule
                    $booking = Booking::create([
                        'booking_code' => 'BKG-' . strtoupper(substr($city, 0, 3)) . '-' . $schedule->id . '-' . rand(1000, 9999),
                        'schedule_id' => $schedule->id,
                        'agent_id' => $agent->id,
                        'user_id' => $bookerUser->id,
                        'booker_name' => 'Pemesan ' . $city . ' Hari ' . ($day + 1),
                        'booker_phone' => '0812' . rand(10000000, 99999999),
                        'total_passengers' => 10,
                        'total_cargo' => 30,
                        'cargo_fee' => 30 * 50000,
                        'subtotal_price' => 10 * $route->base_price,
                        'total_price' => (10 * $route->base_price) + (30 * 50000),
                        'payment_status' => 'paid',
                        'status' => 'confirmed'
                    ]);

                    // Create 30 Cargos
                    for ($c = 1; $c <= 30; $c++) {
                        Cargo::create([
                            'booking_id' => $booking->id,
                            'origin_agent_id' => $route->origin_agent_id,
                            'destination_agent_id' => $route->destination_agent_id,
                            'description' => 'Paket Dummy ' . $c . ' dari ' . $city,
                            'item_name' => 'Dus Sedang',
                            'quantity' => 1,
                            'fee' => 50000,
                            'payment_type' => 'paid_origin',
                            'is_paid' => true,
                            'status' => 'pending',
                            'tracking_code' => 'TRK-' . strtoupper(substr($city, 0, 3)) . '-' . $schedule->id . '-' . str_pad($c, 3, '0', STR_PAD_LEFT) . rand(10,99),
                        ]);
                    }

                    // Create 10 Passengers
                    for ($p = 1; $p <= 10; $p++) {
                        Passenger::create([
                            'booking_id' => $booking->id,
                            'name' => 'Penumpang ' . $p . ' via ' . $city,
                            'gender' => $p % 2 == 0 ? 'male' : 'female',
                            'passenger_type' => 'dewasa',
                            'phone' => '0899' . rand(1000000, 9999999),
                            'seat_number' => 'A' . $p,
                            'is_booker' => $p === 1 ? true : false,
                        ]);
                    }
                }
            }
        });
    }
}
