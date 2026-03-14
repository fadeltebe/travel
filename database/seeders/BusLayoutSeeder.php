<?php

namespace Database\Seeders;

use App\Models\BusLayout;
use App\Models\BusLayoutSeat;
use Illuminate\Database\Seeder;

class BusLayoutSeeder extends Seeder
{
    public function run(): void
    {
        // Layout Elf 15 Seat (4 kolom, baris 1=driver, baris 2-5=penumpang)
        $elf = BusLayout::create([
            'name'          => 'Layout Elf 15 Seat',
            'type'          => 'Elf',
            'total_rows'    => 5,
            'total_columns' => 4,
            'total_seats'   => 15,
            'description'   => 'Layout standar Elf 15 kursi',
            'is_active'     => true,
        ]);

        $this->createElfSeats($elf->id);

        // Layout Medium 25 Seat
        $medium = BusLayout::create([
            'name'          => 'Layout Medium 25 Seat',
            'type'          => 'Medium',
            'total_rows'    => 7,
            'total_columns' => 4,
            'total_seats'   => 25,
            'description'   => 'Layout standar Medium Bus 25 kursi',
            'is_active'     => true,
        ]);

        $this->createMediumSeats($medium->id);
    }

    private function createElfSeats(int $layoutId): void
    {
        // Baris 1: Driver + kosong + penumpang + penumpang
        BusLayoutSeat::create([
            'bus_layout_id' => $layoutId,
            'row' => 1,
            'column' => 1,
            'type' => 'driver',
            'label' => 'Sopir',
            'seat_number' => null,
            'capacity' => 1,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $layoutId,
            'row' => 1,
            'column' => 2,
            'type' => 'aisle',
            'label' => null,
            'seat_number' => null,
            'capacity' => 0,
            'is_available' => false,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $layoutId,
            'row' => 1,
            'column' => 3,
            'type' => 'passenger',
            'label' => null,
            'seat_number' => 'A1',
            'capacity' => 1,
            'is_available' => true,
        ]);
        BusLayoutSeat::create([
            'bus_layout_id' => $layoutId,
            'row' => 1,
            'column' => 4,
            'type' => 'passenger',
            'label' => null,
            'seat_number' => 'A2',
            'capacity' => 1,
            'is_available' => true,
        ]);

        // Baris 2-5: 2 kursi kiri + gang + 2 kursi kanan
        $seatLabels = ['B', 'C', 'D', 'E'];
        $seatNum = 3;
        foreach ($seatLabels as $rowIndex => $label) {
            $row = $rowIndex + 2;
            BusLayoutSeat::create([
                'bus_layout_id' => $layoutId,
                'row' => $row,
                'column' => 1,
                'type' => 'passenger',
                'seat_number' => "{$label}1",
                'capacity' => 1,
                'is_available' => true,
            ]);
            BusLayoutSeat::create([
                'bus_layout_id' => $layoutId,
                'row' => $row,
                'column' => 2,
                'type' => 'passenger',
                'seat_number' => "{$label}2",
                'capacity' => 1,
                'is_available' => true,
            ]);
            BusLayoutSeat::create([
                'bus_layout_id' => $layoutId,
                'row' => $row,
                'column' => 3,
                'type' => 'aisle',
                'label' => null,
                'seat_number' => null,
                'capacity' => 0,
                'is_available' => false,
            ]);
            BusLayoutSeat::create([
                'bus_layout_id' => $layoutId,
                'row' => $row,
                'column' => 4,
                'type' => 'passenger',
                'seat_number' => "{$label}3",
                'capacity' => 1,
                'is_available' => true,
            ]);
        }
    }

    private function createMediumSeats(int $layoutId): void
    {
        // Baris 1: Driver
        BusLayoutSeat::create([
            'bus_layout_id' => $layoutId,
            'row' => 1,
            'column' => 1,
            'type' => 'driver',
            'label' => 'Sopir',
            'seat_number' => null,
            'capacity' => 1,
            'is_available' => false,
        ]);
        for ($col = 2; $col <= 4; $col++) {
            BusLayoutSeat::create([
                'bus_layout_id' => $layoutId,
                'row' => 1,
                'column' => $col,
                'type' => 'aisle',
                'seat_number' => null,
                'capacity' => 0,
                'is_available' => false,
            ]);
        }

        // Baris 2-7: 2+2 layout
        $rowLabels = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($rowLabels as $index => $label) {
            $row = $index + 2;
            foreach ([1, 2, 3, 4] as $col) {
                if ($col === 3) {
                    BusLayoutSeat::create([
                        'bus_layout_id' => $layoutId,
                        'row' => $row,
                        'column' => $col,
                        'type' => 'aisle',
                        'seat_number' => null,
                        'capacity' => 0,
                        'is_available' => false,
                    ]);
                } else {
                    $seatCol = $col > 3 ? $col - 1 : $col;
                    BusLayoutSeat::create([
                        'bus_layout_id' => $layoutId,
                        'row' => $row,
                        'column' => $col,
                        'type' => 'passenger',
                        'seat_number' => "{$label}{$seatCol}",
                        'capacity' => 1,
                        'is_available' => true,
                    ]);
                }
            }
        }
    }
}
