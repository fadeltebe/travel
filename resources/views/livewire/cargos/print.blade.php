<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Cargo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

layout('layouts.blank');

state(['cargo' => null]);

mount(function (Cargo $cargo) {
    // Memastikan relasi termuat
    $this->cargo = $cargo->load(['booking', 'originAgent', 'destinationAgent']);
});

$getQrCode = function() {
    return QrCode::size(120)->margin(0)->generate(url('/cek-resi?trackingCode=' . $this->cargo->tracking_code));
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
            <div class="font-black" style="font-size: 16px;">KARGO RESMI</div>
            <div>{{ $cargo->originAgent->city ?? 'Agen' }}</div>
        </div>
        
        <div class="divider"></div>
        
        <div class="text-center mt-2 mb-2 qrcode">
            {!! $this->getQrCode() !!}
        </div>
        <div class="text-center font-black mt-1" style="font-size: 14px; letter-spacing: 1px;">
            {{ $cargo->tracking_code }}
        </div>
        <div class="text-center" style="font-size: 9px;">
            {{ $cargo->created_at->format('d/m/Y H:i') }} WIB
        </div>

        <div class="divider mt-2"></div>

        <div class="mb-1">
            <div><span class="font-bold">PENGIRIM:</span></div>
            <div class="font-bold">{{ $cargo->booking->booker_name }}</div>
            <div>{{ $cargo->booking->booker_phone ?? '-' }}</div>
        </div>
        <div>
            <div><span class="font-bold">PENERIMA:</span></div>
            <div class="font-bold">{{ $cargo->recipient_name }}</div>
            <div>{{ $cargo->recipient_phone ?? '-' }}</div>
            <div class="font-black mt-1">TUJUAN: {{ $cargo->destinationAgent->city ?? '-' }}</div>
        </div>

        <div class="divider mt-2"></div>

        <div class="font-black mt-1 mb-1 uppercase">{{ $cargo->item_name ?? 'PAKET' }}</div>
        <div style="font-size: 10px;">{{ $cargo->description }}</div>
        <div class="d-flex mt-1 font-bold">
            <span>Berat:</span>
            <span>{{ $cargo->weight_kg }} KG</span>
        </div>
        <div class="d-flex font-bold">
            <span>Koli:</span>
            <span>{{ $cargo->quantity }} BOX</span>
        </div>

        <div class="divider"></div>

        <div class="d-flex font-black mt-1" style="font-size: 12px;">
            <span>TOTAL:</span>
            <span>Rp{{ number_format($cargo->fee, 0, ',', '.') }}</span>
        </div>
        
        <div class="text-center mt-2 mb-2">
            <div class="font-black" style="font-size:12px; border: 1.5px solid #000; padding: 2px 4px; display: inline-block;">
                {{ $cargo->is_paid ? 'LUNAS' : 'BELUM LUNAS' }}
            </div>
        </div>

        <div class="divider"></div>
        
        <div class="text-center mt-2" style="font-size: 9px;">
            Harap simpan resi ini sebagai <br>bukti pengambilan barang.
        </div>
        
        <div class="text-center mt-2 print:hidden mb-1">
            <button onclick="window.print()" style="padding: 6px 12px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;">
                CETAK SEKARANG
            </button>
            <div style="margin-top: 8px;">
                <a href="{{ route('cargo.show', $cargo->tracking_code) }}" style="color: #666; text-decoration: underline; font-size: 11px;">Kembali ke Detail</a>
            </div>
        </div>
    </div>
</div>
