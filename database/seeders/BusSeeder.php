<?php

namespace Database\Seeders;

use App\Models\Bus;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Harapan 01 - Elf dengan Layout Elf 15 Seat
        Bus::create([
            'plate_number' => 'DT 1001 AB',
            'name' => 'Harapan 01',
            'type' => 'Elf',
            'total_seats' => 15,
            'bus_layout_id' => 1, // Layout Elf 15 Seat
            'is_active' => true,
        ]);

        // Harapan 02 - Elf dengan Layout Elf 15 Seat
        Bus::create([
            'plate_number' => 'DT 1002 AB',
            'name' => 'Harapan 02',
            'type' => 'Elf',
            'total_seats' => 15,
            'bus_layout_id' => 1, // Layout Elf 15 Seat
            'is_active' => true,
        ]);

        // Harapan 03 - Medium dengan Layout Medium 25 Seat
        Bus::create([
            'plate_number' => 'DT 1003 AB',
            'name' => 'Harapan 03',
            'type' => 'Medium',
            'total_seats' => 25,
            'bus_layout_id' => 2, // Layout Medium 25 Seat
            'is_active' => true,
        ]);

        // Harapan 04 - Medium dengan Layout Medium 25 Seat
        Bus::create([
            'plate_number' => 'DT 1004 AB',
            'name' => 'Harapan 04',
            'type' => 'Medium',
            'total_seats' => 25,
            'bus_layout_id' => 2, // Layout Medium 25 Seat
            'is_active' => true,
        ]);

        // Harapan 05 - Big Bus dengan Layout Big Bus 47 Seat
        Bus::create([
            'plate_number' => 'DT 1005 AB',
            'name' => 'Harapan 05',
            'type' => 'Big Bus',
            'total_seats' => 47,
            'bus_layout_id' => 3, // Layout Big Bus 47 Seat
            'is_active' => true,
        ]);

        // Harapan 06 - Big Bus dengan Layout Big Bus 47 Seat
        Bus::create([
            'plate_number' => 'DT 1006 AB',
            'name' => 'Harapan 06',
            'type' => 'Big Bus',
            'total_seats' => 47,
            'bus_layout_id' => 3, // Layout Big Bus 47 Seat
            'is_active' => true,
        ]);
    }
}
