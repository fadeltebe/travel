<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Agent;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::where('payment_status', 'paid')->limit(10)->get();

        if ($bookings->isEmpty()) {
            return;
        }

        $methods = ['cash', 'transfer', 'qris'];

        foreach ($bookings as $booking) {
            // Payment untuk booking
            $agent = Agent::inRandomOrder()->first();

            Payment::create([
                'booking_id' => $booking->id,
                'payment_type' => 'booking',
                'amount' => $booking->subtotal_price,
                'method' => $methods[array_rand($methods)],
                'reference_number' => 'REF-' . uniqid(),
                'paid_by' => $booking->booker_name,
                'received_by' => null,
                'agent_id' => $agent->id,
                'paid_at' => $booking->paid_at ?? now(),
                'proof_photo' => null,
                'notes' => 'Pembayaran booking tiket',
            ]);

            // Payment untuk cargo COD (jika ada)
            $cargos = $booking->cargos()
                ->where('payment_type', 'paid_destination')
                ->where('is_paid', true)
                ->get();

            foreach ($cargos as $cargo) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'payment_type' => 'cargo_cod',
                    'amount' => $cargo->fee + $cargo->pickup_fee + $cargo->dropoff_fee,
                    'method' => $methods[array_rand($methods)],
                    'reference_number' => 'COD-' . uniqid(),
                    'paid_by' => null,
                    'received_by' => null,
                    'agent_id' => $cargo->destination_agent_id,
                    'paid_at' => $cargo->paid_at ?? now(),
                    'proof_photo' => null,
                    'notes' => 'Pembayaran COD cargo',
                ]);
            }
        }
    }
}
