<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\CargoReceipt;
use App\Models\Agent;
use Illuminate\Database\Seeder;

class CargoReceiptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cargos = Cargo::where('status', 'arrived')
            ->orWhere('status', 'received')
            ->limit(5)
            ->get();

        if ($cargos->isEmpty()) {
            return;
        }

        foreach ($cargos as $cargo) {
            CargoReceipt::create([
                'cargo_id' => $cargo->id,
                'receipt_number' => 'RECEIPT-' . $cargo->id . '-' . now()->format('Ymd'),
                'qr_code' => 'QR_' . uniqid(),
                'received_by_name' => 'Penerima ' . $cargo->id,
                'received_by_phone' => '089' . rand(100000000, 999999999),
                'received_at' => now(),
                'agent_id' => $cargo->destination_agent_id,
                'handler_user_id' => null,
                'signature_photo' => null,
                'notes' => 'Barang diterima dengan baik',
            ]);
        }
    }
}
