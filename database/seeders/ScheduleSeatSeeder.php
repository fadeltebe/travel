<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Bus;
use App\Models\Customer;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\SeatBooking;
use App\Models\BusLayoutSeat;
use Illuminate\Database\Seeder;

class ScheduleSeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data yang sudah ada
        $route = Route::first() ?? Route::create([
            'origin_city' => 'Palu',
            'destination_city' => 'Makassar',
            'distance_km' => 240,
            'estimated_duration_minutes' => 360,
            'base_price' => 150000,
            'is_active' => true,
        ]);

        $bus = Bus::first();
        $agent = Agent::first();
        $customer = Customer::firstOrCreate(
            ['phone' => '08123456789'],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'id_card_number' => '1234567890123456',
                'address' => 'Jl. Ahmad Yani 123, Palu',
                'is_active' => true,
            ]
        );

        // Buat schedule
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'bus_id' => $bus->id,
            'departure_date' => now()->addDay()->format('Y-m-d'),
            'departure_time' => '08:00:00',
            'arrival_time' => '14:00:00',
            'price' => 150000,
            'available_seats' => $bus->total_seats,
            'status' => 'scheduled',
        ]);

        // Buat booking
        $booking = Booking::create([
            'booking_code' => 'TRV-' . now()->format('Ymd') . '-001',
            'schedule_id' => $schedule->id,
            'agent_id' => $agent->id,
            'customer_id' => $customer->id,
            'booker_name' => 'John Doe',
            'booker_phone' => '08123456789',
            'booker_email' => 'john@example.com',
            'total_passengers' => 3,
            'total_cargo' => 0,
            'subtotal_price' => 450000, // 3 x 150000
            'cargo_fee' => 0,
            'cargo_cod_fee' => 0,
            'pickup_dropoff_fee' => 0,
            'total_price' => 450000,
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
            'paid_at' => now(),
            'status' => 'confirmed',
        ]);

        // Get available seats dari layout
        $busSeats = BusLayoutSeat::where('bus_layout_id', $bus->seat_layout_id)
            ->where('is_available', true)
            ->where('type', '!=', 'door')
            ->where('type', '!=', 'aisle')
            ->where('type', '!=', 'driver')
            ->limit(3)
            ->get();

        // Buat 3 passenger dengan seat booking
        $passengerNames = ['Budi', 'Siti', 'Ahmad'];
        foreach ($busSeats as $index => $busLayoutSeat) {
            $passenger = Passenger::create([
                'booking_id' => $booking->id,
                'name' => $passengerNames[$index],
                'id_card_number' => '12345678901234' . ($index + 1),
                'phone' => '0812345678' . ($index + 1),
                'seat_number' => $busLayoutSeat->seat_number,
                'is_booker' => $index === 0,
                'pickup_address' => 'Jl. Ahmad Yani 123, Palu',
                'dropoff_address' => 'Jl. Merdeka 456, Makassar',
                'pickup_fee' => 0,
                'dropoff_fee' => 0,
                'need_pickup' => true,
                'need_dropoff' => true,
            ]);

            // Create seat booking
            SeatBooking::create([
                'schedule_id' => $schedule->id,
                'passenger_id' => $passenger->id,
                'bus_layout_seat_id' => $busLayoutSeat->id,
                'seat_number' => $busLayoutSeat->seat_number,
                'status' => 'booked',
            ]);
        }

        // Update schedule available seats
        $schedule->update([
            'available_seats' => $bus->total_seats - 3,
        ]);
    }
}
