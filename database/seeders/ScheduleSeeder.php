<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cargo;
use App\Models\User;
use App\Models\Agent;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $buses   = Bus::all();
        $routes  = Route::all();
        $drivers = User::where('role', Role::Driver)->get();

        $statuses = ['scheduled', 'ongoing', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed'];
        $cargoStatuses = ['pending', 'in_transit', 'arrived', 'received'];

        $months = [1, 2, 3]; // Jan, Feb, Mar
        $year = now()->year;

        foreach ($months as $month) {
            for ($i = 1; $i <= 10; $i++) {
                $route = $routes->random();
                $bus = $buses->random();
                $driver = $drivers->random();
                
                $day = rand(1, 28);
                $date = Carbon::create($year, $month, $day);
                
                $schedule = Schedule::create([
                    'route_id'        => $route->id,
                    'bus_id'          => $bus->id,
                    'driver_id'       => $driver->id,
                    'departure_date'  => $date->format('Y-m-d'),
                    'departure_time'  => '08:00',
                    'arrival_date'    => $date->copy()->addMinutes($route->estimated_duration_minutes)->format('Y-m-d'),
                    'arrival_time'    => $date->copy()->addMinutes($route->estimated_duration_minutes)->format('H:i'),
                    'price'           => $route->base_price,
                    'available_seats' => $bus->total_seats,
                    'status'          => $statuses[array_rand($statuses)],
                    'created_at'      => $date->copy()->subDays(5),
                    'updated_at'      => $date->copy()->subDays(5),
                ]);

                // 1. Create Passenger Bookings (random 3-7 bookings)
                $seatsTaken = [];
                $numPassBookings = rand(3, 7);
                for ($b = 1; $b <= $numPassBookings; $b++) {
                    $numPassengers = rand(1, 3);
                    if (count($seatsTaken) + $numPassengers > $bus->total_seats) break;

                    $pStatus = $paymentStatuses[array_rand($paymentStatuses)];
                    $totalPrice = $numPassengers * $schedule->price;
                    
                    $pBooking = Booking::create([
                        'booking_code'     => 'TRV-' . $date->format('Ymd') . '-' . strtoupper(Str::random(4)),
                        'schedule_id'      => $schedule->id,
                        'agent_id'         => $route->origin_agent_id,
                        'booker_name'      => 'Penumpang P' . $b . Str::random(2),
                        'booker_phone'     => '0812' . rand(100000, 999999),
                        'total_passengers' => $numPassengers,
                        'total_cargo'      => 0,
                        'subtotal_price'   => $totalPrice,
                        'total_price'      => $totalPrice,
                        'payment_status'   => $pStatus,
                        'created_at'       => $date->copy()->subDays(rand(1, 4)),
                        'updated_at'       => $date->copy()->subDays(rand(1, 4)),
                    ]);

                    for ($p = 1; $p <= $numPassengers; $p++) {
                        $seat = rand(1, $bus->total_seats);
                        while(in_array($seat, $seatsTaken)) { $seat = rand(1, $bus->total_seats); }
                        $seatsTaken[] = $seat;

                        Passenger::create([
                            'booking_id'  => $pBooking->id,
                            'name'        => 'Penumpang ' . Str::random(3),
                            'phone'       => '0852' . rand(100000, 999999),
                            'seat_number' => $seat,
                            'created_at'  => $pBooking->created_at,
                            'updated_at'  => $pBooking->updated_at,
                        ]);
                    }
                }
                
                $schedule->update(['available_seats' => $bus->total_seats - count($seatsTaken)]);

                // 2. Create Exactly 30 Cargos per Schedule
                for ($c = 1; $c <= 30; $c++) {
                    $weight = rand(1, 20);
                    $fee = $weight * 5000;
                    $isPaid = rand(0, 1) == 1;
                    $cStatus = $cargoStatuses[array_rand($cargoStatuses)];
                    
                    // Buat Booking khusus Cargo
                    $cBooking = Booking::create([
                        'booking_code'     => 'CRG-' . $date->format('Ymd') . '-' . strtoupper(Str::random(4)),
                        'schedule_id'      => $schedule->id,
                        'agent_id'         => $route->origin_agent_id,
                        'booker_name'      => 'Pengirim Kargo ' . $c,
                        'booker_phone'     => '0813' . rand(100000, 999999),
                        'total_passengers' => 0,
                        'total_cargo'      => 1,
                        'cargo_fee'        => $fee,
                        'subtotal_price'   => $fee,
                        'total_price'      => $fee,
                        'payment_status'   => $isPaid ? 'paid' : 'pending',
                        'created_at'       => $date->copy()->subDays(rand(0, 2)),
                        'updated_at'       => $date->copy()->subDays(rand(0, 2)),
                    ]);

                    Cargo::create([
                        'tracking_code'        => 'RESI' . strtoupper(Str::random(8)),
                        'booking_id'           => $cBooking->id,
                        'origin_agent_id'      => $route->origin_agent_id,
                        'destination_agent_id' => $route->destination_agent_id,
                        'description'          => 'Barang Cargo ' . array_rand(['Dokumen'=>1, 'Elektronik'=>1, 'Makanan'=>1, 'Pakaian'=>1]),
                        'weight_kg'            => $weight,
                        'quantity'             => 1,
                        'fee'                  => $fee,
                        'recipient_name'       => 'Penerima Kargo ' . $c,
                        'recipient_phone'      => '0853' . rand(100000, 999999),
                        'payment_type'         => 'paid_origin',
                        'payment_method'       => 'cash',
                        'is_paid'              => $isPaid,
                        'paid_at'              => $isPaid ? $cBooking->created_at : null,
                        'status'               => $cStatus,
                        'created_at'           => $cBooking->created_at,
                        'updated_at'           => $cBooking->updated_at,
                    ]);
                }
            }
        }
    }
}
