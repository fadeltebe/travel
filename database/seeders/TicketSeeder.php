<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $passengers = Passenger::limit(10)->get();

        if ($passengers->isEmpty()) {
            return;
        }

        foreach ($passengers as $passenger) {
            Ticket::create([
                'booking_id' => $passenger->booking_id,
                'passenger_id' => $passenger->id,
                'ticket_number' => 'TKT-' . $passenger->booking_id . '-' . $passenger->id,
                'qr_code' => 'QR_TICKET_' . uniqid(),
                'status' => rand(0, 1) ? 'active' : 'used',
                'scanned_at' => rand(0, 1) ? now() : null,
                'scanned_by' => null,
            ]);
        }
    }
}
