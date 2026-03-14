<?php

namespace Database\Seeders;

use App\Models\BusLayout;
use App\Models\BusLayoutSeat;
use Illuminate\Database\Seeder;

class BusLayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Layout Elf 15 Seat
        $elf15 = BusLayout::create([
            'name' => 'Layout Elf 15 Seat',
            'type' => 'Elf',
            'total_rows' => 5,
            'total_columns' => 3,
            'total_seats' => 14, // 1 driver + 13 passenger (excluding door)
            'description' => 'Layout untuk Elf 15: Sopir + 2 penumpang row 1, Pintu + 2 penumpang row 2, 3 baris full penumpang',
            'is_active' => true,
        ]);

        // Row 1: Driver + 2 passenger
        BusLayoutSeat::create([
            'bus_layout_id' => $elf15->id,
            'row' => 1,
            'column' => 1,
            'seat_number' => null,
            'type' => 'driver',
            'label' => 'Sopir',
            'capacity' => 1,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $elf15->id,
            'row' => 1,
            'column' => 2,
            'seat_number' => 'A1',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $elf15->id,
            'row' => 1,
            'column' => 3,
            'seat_number' => 'A2',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);

        // Row 2: Door + 2 passenger
        BusLayoutSeat::create([
            'bus_layout_id' => $elf15->id,
            'row' => 2,
            'column' => 1,
            'seat_number' => null,
            'type' => 'door',
            'label' => 'Pintu Masuk',
            'capacity' => 0,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $elf15->id,
            'row' => 2,
            'column' => 2,
            'seat_number' => 'B1',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $elf15->id,
            'row' => 2,
            'column' => 3,
            'seat_number' => 'B2',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);

        // Row 3: 3 passenger
        for ($col = 1; $col <= 3; $col++) {
            BusLayoutSeat::create([
                'bus_layout_id' => $elf15->id,
                'row' => 3,
                'column' => $col,
                'seat_number' => 'C' . $col,
                'type' => 'passenger',
                'label' => null,
                'capacity' => 1,
                'is_available' => true,
            ]);
        }

        // Row 4: 3 passenger
        for ($col = 1; $col <= 3; $col++) {
            BusLayoutSeat::create([
                'bus_layout_id' => $elf15->id,
                'row' => 4,
                'column' => $col,
                'seat_number' => 'D' . $col,
                'type' => 'passenger',
                'label' => null,
                'capacity' => 1,
                'is_available' => true,
            ]);
        }

        // Row 5: 3 passenger
        for ($col = 1; $col <= 3; $col++) {
            BusLayoutSeat::create([
                'bus_layout_id' => $elf15->id,
                'row' => 5,
                'column' => $col,
                'seat_number' => 'E' . $col,
                'type' => 'passenger',
                'label' => null,
                'capacity' => 1,
                'is_available' => true,
            ]);
        }

        // ========================================
        // Layout Medium 25 Seat
        // ========================================
        $medium25 = BusLayout::create([
            'name' => 'Layout Medium 25 Seat',
            'type' => 'Medium',
            'total_rows' => 9,
            'total_columns' => 3,
            'total_seats' => 24, // 1 driver + 20 passenger + 1 long seat (capacity 3)
            'description' => 'Layout untuk Medium: Sopir + 2 penumpang row 1, Pintu + 2 penumpang row 2, 6 baris full penumpang, 1 kursi panjang',
            'is_active' => true,
        ]);

        // Row 1: Driver + 2 passenger
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 1,
            'column' => 1,
            'seat_number' => null,
            'type' => 'driver',
            'label' => 'Sopir',
            'capacity' => 1,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 1,
            'column' => 2,
            'seat_number' => 'A1',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 1,
            'column' => 3,
            'seat_number' => 'A2',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);

        // Row 2: Door + 2 passenger
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 2,
            'column' => 1,
            'seat_number' => null,
            'type' => 'door',
            'label' => 'Pintu Masuk',
            'capacity' => 0,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 2,
            'column' => 2,
            'seat_number' => 'B1',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 2,
            'column' => 3,
            'seat_number' => 'B2',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);

        // Row 3-8: Full passenger (6 rows)
        $seatLabel = 'C';
        for ($row = 3; $row <= 8; $row++) {
            for ($col = 1; $col <= 3; $col++) {
                BusLayoutSeat::create([
                    'bus_layout_id' => $medium25->id,
                    'row' => $row,
                    'column' => $col,
                    'seat_number' => $seatLabel . $col,
                    'type' => 'passenger',
                    'label' => null,
                    'capacity' => 1,
                    'is_available' => true,
                ]);
            }
            $seatLabel++;
        }

        // Row 9: Long seat (3 orang dalam 1 kursi)
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 9,
            'column' => 1,
            'seat_number' => 'I',
            'type' => 'long_seat',
            'label' => 'Kursi Panjang (3 orang)',
            'capacity' => 3,
            'is_available' => true,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 9,
            'column' => 2,
            'seat_number' => null,
            'type' => 'aisle',
            'label' => 'Gang',
            'capacity' => 0,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $medium25->id,
            'row' => 9,
            'column' => 3,
            'seat_number' => null,
            'type' => 'aisle',
            'label' => 'Gang',
            'capacity' => 0,
            'is_available' => false,
        ]);

        // ========================================
        // Layout Big Bus 47 Seat
        // ========================================
        $big47 = BusLayout::create([
            'name' => 'Layout Big Bus 47 Seat',
            'type' => 'Big Bus',
            'total_rows' => 16,
            'total_columns' => 3,
            'total_seats' => 46, // 1 driver + 42 passenger + 1 kursi panjang (3 orang)
            'description' => 'Layout untuk Big Bus: Sopir + 2 penumpang, 14 baris full penumpang + 1 kursi panjang',
            'is_active' => true,
        ]);

        // Row 1: Driver + 2 passenger
        BusLayoutSeat::create([
            'bus_layout_id' => $big47->id,
            'row' => 1,
            'column' => 1,
            'seat_number' => null,
            'type' => 'driver',
            'label' => 'Sopir',
            'capacity' => 1,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $big47->id,
            'row' => 1,
            'column' => 2,
            'seat_number' => 'A1',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $big47->id,
            'row' => 1,
            'column' => 3,
            'seat_number' => 'A2',
            'type' => 'passenger',
            'label' => null,
            'capacity' => 1,
            'is_available' => true,
        ]);

        // Row 2: Full passenger
        for ($col = 1; $col <= 3; $col++) {
            BusLayoutSeat::create([
                'bus_layout_id' => $big47->id,
                'row' => 2,
                'column' => $col,
                'seat_number' => 'B' . $col,
                'type' => 'passenger',
                'label' => null,
                'capacity' => 1,
                'is_available' => true,
            ]);
        }

        // Row 3-15: Full passenger (13 rows)
        $seatLabel = 'C';
        for ($row = 3; $row <= 15; $row++) {
            for ($col = 1; $col <= 3; $col++) {
                BusLayoutSeat::create([
                    'bus_layout_id' => $big47->id,
                    'row' => $row,
                    'column' => $col,
                    'seat_number' => $seatLabel . $col,
                    'type' => 'passenger',
                    'label' => null,
                    'capacity' => 1,
                    'is_available' => true,
                ]);
            }
            $seatLabel++;
        }

        // Row 16: Long seat (3 orang)
        BusLayoutSeat::create([
            'bus_layout_id' => $big47->id,
            'row' => 16,
            'column' => 1,
            'seat_number' => 'O',
            'type' => 'long_seat',
            'label' => 'Kursi Panjang (3 orang)',
            'capacity' => 3,
            'is_available' => true,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $big47->id,
            'row' => 16,
            'column' => 2,
            'seat_number' => null,
            'type' => 'aisle',
            'label' => 'Gang',
            'capacity' => 0,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $big47->id,
            'row' => 16,
            'column' => 3,
            'seat_number' => null,
            'type' => 'aisle',
            'label' => 'Gang',
            'capacity' => 0,
            'is_available' => false,
        ]);
    }
}
