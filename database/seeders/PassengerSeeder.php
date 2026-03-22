<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Passenger;
use Illuminate\Database\Seeder;

class PassengerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::all();

        if ($bookings->isEmpty()) {
            return;
        }

        $maleNames = [
            'Ahmad Fauzi', 'Budi Santoso', 'Cahyo Wibowo', 'Dimas Prayoga',
            'Eko Prasetyo', 'Fajar Nugroho', 'Gunawan Setiawan', 'Hendra Wijaya',
            'Irfan Hakim', 'Joko Susilo',
        ];

        $femaleNames = [
            'Ani Rahmawati', 'Bella Putri', 'Citra Dewi', 'Dina Maharani',
            'Eka Sari', 'Fitri Handayani', 'Gina Puspita', 'Hani Safitri',
            'Intan Permata', 'Juliana Sari',
        ];

        $childNames = [
            'Rafi', 'Zahra', 'Aqila', 'Danish', 'Naura',
            'Azka', 'Keyla', 'Alby', 'Sakha', 'Fatin',
        ];

        $addresses = [
            'Jl. Merdeka No. 10, RT 01/RW 02',
            'Jl. Sudirman No. 45, Kel. Sukamaju',
            'Jl. Gatot Subroto No. 78',
            'Jl. Diponegoro No. 23, Kel. Cempaka',
            'Jl. Ahmad Yani No. 56',
            'Jl. Pahlawan No. 12, RT 05/RW 03',
            'Jl. Kartini No. 89',
            'Jl. Imam Bonjol No. 34',
        ];

        $seatLetters = ['A', 'B', 'C', 'D'];

        foreach ($bookings as $booking) {
            $passengerCount = rand(1, 4);

            for ($i = 0; $i < $passengerCount; $i++) {
                $passengerType = $this->randomPassengerType();
                $gender = rand(0, 1) ? 'male' : 'female';

                // Pilih nama sesuai tipe dan gender
                if ($passengerType === 'balita' || $passengerType === 'anak-anak') {
                    $name = $childNames[array_rand($childNames)];
                } elseif ($gender === 'male') {
                    $name = $maleNames[array_rand($maleNames)];
                } else {
                    $name = $femaleNames[array_rand($femaleNames)];
                }

                $isBooker = $i === 0; // Penumpang pertama = pemesan
                $needPickup = rand(0, 1) ? true : false;
                $needDropoff = rand(0, 1) ? true : false;
                $seatNumber = $seatLetters[array_rand($seatLetters)] . ($i + 1);

                Passenger::create([
                    'booking_id'      => $booking->id,
                    'name'            => $name,
                    'gender'          => $gender,
                    'passenger_type'  => $passengerType,
                    'id_card_number'  => $passengerType === 'dewasa' ? $this->generateNIK() : null,
                    'phone'           => $passengerType === 'dewasa' ? '08' . rand(1000000000, 9999999999) : null,
                    'seat_number'     => $seatNumber,
                    'is_booker'       => $isBooker,
                    'pickup_address'  => $needPickup ? $addresses[array_rand($addresses)] : null,
                    'dropoff_address' => $needDropoff ? $addresses[array_rand($addresses)] : null,
                    'pickup_fee'      => $needPickup ? rand(1, 5) * 10000 : 0,
                    'dropoff_fee'     => $needDropoff ? rand(1, 5) * 10000 : 0,
                    'need_pickup'     => $needPickup,
                    'need_dropoff'    => $needDropoff,
                ]);
            }
        }
    }

    /**
     * Generate random NIK (16 digit).
     */
    private function generateNIK(): string
    {
        return rand(3100, 9999) . rand(10, 99) . rand(100000, 999999) . rand(1000, 9999);
    }

    /**
     * Random passenger type dengan bobot: dewasa lebih sering.
     */
    private function randomPassengerType(): string
    {
        $rand = rand(1, 100);

        if ($rand <= 10) {
            return 'balita';       // 10%
        } elseif ($rand <= 25) {
            return 'anak-anak';    // 15%
        }

        return 'dewasa';           // 75%
    }
}
