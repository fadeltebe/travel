<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Cargo;
use App\Models\Agent;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::limit(5)->get();

        if ($bookings->isEmpty()) {
            return; // Skip jika tidak ada booking
        }

        $agentOrigins = Agent::limit(3)->get();
        $agentDestinations = Agent::skip(2)->limit(3)->get();

        $cargoDescriptions = [
            'Paket elektronik (TV 32 inch)',
            'Buah-buahan segar (30kg)',
            'Perlengkapan rumah tangga',
            'Mesin cuci bekas',
            'Buku dan alat tulis',
            'Makanan kering dan bumbu',
            'Pakaian dan tekstil',
            'Peralatan dapur',
        ];

        $index = 0;
        foreach ($bookings as $booking) {
            if ($index >= 10) break;

            $origin = $agentOrigins->random();
            $destination = $agentDestinations->random();

            Cargo::create([
                'booking_id' => $booking->id,
                'origin_agent_id' => $origin->id,
                'destination_agent_id' => $destination->id,
                'description' => $cargoDescriptions[array_rand($cargoDescriptions)],
                'weight_kg' => rand(1, 50),
                'quantity' => rand(1, 5),
                'fee' => rand(25000, 150000),
                'recipient_name' => 'Penerima ' . ($index + 1),
                'recipient_phone' => '089' . rand(100000000, 999999999),
                'pickup_address' => 'Jl. Pickup ' . ($index + 1),
                'dropoff_address' => 'Jl. Dropoff ' . ($index + 1),
                'dropoff_location_name' => 'Lokasi ' . ($index + 1),
                'pickup_fee' => rand(10000, 50000),
                'dropoff_fee' => rand(10000, 50000),
                'need_pickup' => (bool) rand(0, 1),
                'need_dropoff' => (bool) rand(0, 1),
                'payment_type' => rand(0, 1) ? 'paid_origin' : 'paid_destination',
                'payment_method' => ['cash', 'transfer', 'qris'][array_rand(['cash', 'transfer', 'qris'])],
                'is_paid' => rand(0, 1),
                'paid_at' => rand(0, 1) ? now() : null,
                'status' => ['pending', 'in_transit', 'arrived', 'received'][array_rand(['pending', 'in_transit', 'arrived', 'received'])],
                'notes' => 'Catatan cargo ' . ($index + 1),
            ]);

            $index++;
        }
    }
}
