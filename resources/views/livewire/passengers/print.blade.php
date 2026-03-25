<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Passenger;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

layout('layouts.blank');

state(['passenger' => null]);

mount(function (Passenger $passenger) {
    // Memastikan relasi termuat
    $this->passenger = $passenger->load(['booking.schedule.route.originAgent', 'booking.schedule.route.destinationAgent', 'booking.schedule.bus']);
});

$getQrCode = function() {
    return QrCode::size(120)->margin(0)->generate(route('passengers.show', $this->passenger->id));
};

?>

<div>
    {{-- Styling Khusus Printer Thermal 50mm --}}
    <style>
        @page { margin: 0; size: 50mm auto; }
        body { margin: 0; padding: 0; background: #fff; color: #000; font-family: monospace; font-size: 11px; line-height: 1.2; }
        .thermal-receipt { width: 48mm; margin: 0 auto; padding: 2mm; overflow: hidden; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-black { font-weight: 900; }
        .uppercase { text-transform: uppercase; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .d-flex { display: flex; justify-content: space-between; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .qrcode svg { margin: 0 auto; display: block; max-width: 100%; height: auto; }
        
        @media screen {
            body { background: #f3f4f6; display: flex; justify-content: center; padding: 20px; }
            .thermal-receipt { background: #fff; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        }
    </style>

    <div class="thermal-receipt">
        <div class="text-center mb-2">
            <div class="font-black" style="font-size: 16px;">E-TIKET RESMI</div>
            <div>{{ $passenger->booking->schedule->route->originAgent->city ?? 'Agen' }}</div>
        </div>
        
        <div class="divider"></div>
        
        <div class="text-center mt-2 mb-2 qrcode">
            {!! $this->getQrCode() !!}
        </div>
        <div class="text-center font-black mt-1" style="font-size: 14px; letter-spacing: 1px;">
            {{ $passenger->booking->booking_code }}
        </div>
        <div class="text-center" style="font-size: 9px;">
            Cetak: {{ now()->format('d/m/Y H:i') }} WIB
        </div>

        <div class="divider mt-2"></div>

        <div class="mb-1 text-center">
            <div class="font-bold" style="font-size: 13px;">{{ $passenger->name }}</div>
            <div>{{ $passenger->phone ?? '-' }}</div>
        </div>

        <div class="divider mt-2"></div>

        <div class="font-black mt-1 mb-1 text-center" style="font-size: 11px;">RUTE PERJALANAN</div>
        <div class="d-flex mt-1">
            <span class="font-bold">Dari:</span>
            <span>{{ $passenger->booking->schedule->route->originAgent->city ?? '-' }}</span>
        </div>
        <div class="d-flex mt-1">
            <span class="font-bold">Tujuan:</span>
            <span>{{ $passenger->booking->schedule->route->destinationAgent->city ?? '-' }}</span>
        </div>
        
        <div class="d-flex mt-1">
            <span class="font-bold">Keberangkatan:</span>
            <div class="text-right">
                <div>{{ \Carbon\Carbon::parse($passenger->booking->schedule->departure_time)->format('d M Y') }}</div>
                <div>{{ \Carbon\Carbon::parse($passenger->booking->schedule->departure_time)->format('H:i') }} WIB</div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="d-flex font-bold mt-1">
            <span>Bus:</span>
            <span>{{ $passenger->booking->schedule->bus->name ?? '-' }}</span>
        </div>
        
        <div class="text-center mt-2 mb-2">
            <div class="font-black" style="font-size:16px; border: 1.5px solid #000; padding: 4px 8px; display: inline-block;">
                KURSI: {{ $passenger->seat_number ?? 'N/A' }}
            </div>
        </div>

        <div class="text-center mb-2">
            <div class="font-black" style="font-size:11px; display: inline-block;">
                ({{ $passenger->booking->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }})
            </div>
        </div>

        <div class="divider"></div>
        
        <div class="text-center mt-2" style="font-size: 9px;">
            Harap hadir 30 menit sebelum <br>jadwal keberangkatan.
        </div>
        
        <div class="text-center mt-2 print:hidden mb-1">
            <button onclick="window.print()" style="padding: 6px 12px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;">
                CETAK SEKARANG
            </button>
            <div style="margin-top: 8px;">
                <a href="{{ route('passengers.show', $passenger) }}" style="color: #666; text-decoration: underline; font-size: 11px;">Kembali ke Detail</a>
            </div>
        </div>
    </div>
</div>
