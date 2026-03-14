<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\BusLayout;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $elf    = BusLayout::where('type', 'Elf')->first();
        $medium = BusLayout::where('type', 'Medium')->first();

        $buses = [
            [
                'plate_number'   => 'DN 1234 AB',
                'brand'          => 'Toyota',
                'name'           => 'Sulteng Express 01',
                'type'           => 'Elf',
                'bus_layout_id'  => $elf->id,
                'total_seats'    => $elf->total_seats,
                'is_active'      => true,
            ],
            [
                'plate_number'   => 'DN 5678 CD',
                'brand'          => 'Toyota',
                'name'           => 'Sulteng Express 02',
                'type'           => 'Elf',
                'bus_layout_id'  => $elf->id,
                'total_seats'    => $elf->total_seats,
                'is_active'      => true,
            ],
            [
                'plate_number'   => 'DN 9012 EF',
                'brand'          => 'Isuzu',
                'name'           => 'Sulteng Express 03',
                'type'           => 'Medium',
                'bus_layout_id'  => $medium->id,
                'total_seats'    => $medium->total_seats,
                'is_active'      => true,
            ],
        ];

        foreach ($buses as $bus) {
            Bus::create($bus);
        }
    }
}
