<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Budi Santoso',
                'phone' => '08123456789',
                'email' => 'budi@example.com',
                'id_card_number' => '7201012345678901',
                'address' => 'Jl. Ahmad Yani 123, Palu',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'phone' => '08234567890',
                'email' => 'siti@example.com',
                'id_card_number' => '7202012345678902',
                'address' => 'Jl. Gajah Mada 456, Palu',
            ],
            [
                'name' => 'Ahmad Hidayat',
                'phone' => '08345678901',
                'email' => 'ahmad@example.com',
                'id_card_number' => '7203012345678903',
                'address' => 'Jl. Sudirman 789, Donggala',
            ],
            [
                'name' => 'Rini Wijaya',
                'phone' => '08456789012',
                'email' => 'rini@example.com',
                'id_card_number' => '7204012345678904',
                'address' => 'Jl. Merdeka 321, Makassar',
            ],
            [
                'name' => 'Eka Putri',
                'phone' => '08567890123',
                'email' => 'eka@example.com',
                'id_card_number' => '7205012345678905',
                'address' => 'Jl. Lombok 654, Makassar',
            ],
            [
                'name' => 'Doni Hermawan',
                'phone' => '08678901234',
                'email' => 'doni@example.com',
                'id_card_number' => '7206012345678906',
                'address' => 'Jl. Hasanuddin 987, Gowa',
            ],
            [
                'name' => 'Lisa Mara',
                'phone' => '08789012345',
                'email' => 'lisa@example.com',
                'id_card_number' => '7207012345678907',
                'address' => 'Jl. Diponegoro 147, Sungguminasa',
            ],
            [
                'name' => 'Hendra Gunawan',
                'phone' => '08890123456',
                'email' => 'hendra@example.com',
                'id_card_number' => '7208012345678908',
                'address' => 'Jl. Kartini 258, Ampana',
            ],
            [
                'name' => 'Maya Sari',
                'phone' => '08901234567',
                'email' => 'maya@example.com',
                'id_card_number' => '7209012345678909',
                'address' => 'Jl. Panglima 369, Palu',
            ],
            [
                'name' => 'Yudha Pratama',
                'phone' => '08912345678',
                'email' => 'yudha@example.com',
                'id_card_number' => '7210012345678910',
                'address' => 'Jl. Imam Bonjol 741, Makassar',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create([
                ...$customer,
                'is_active' => true,
                'total_bookings' => 0,
                'total_trips' => 0,
                'total_shipments' => 0,
            ]);
        }
    }
}
