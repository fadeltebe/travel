<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Cargo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

layout('layouts.blank');

state(['cargo' => null, 'company' => null]);

mount(function (Cargo $cargo) {
    // Memastikan relasi termuat
    $this->cargo = $cargo->load(['booking', 'originAgent', 'destinationAgent']);
    $this->company = \App\Models\Company::first();
});

?>

<div>
    {{-- Styling Khusus Printer Thermal 50mm --}}
    <style>
        @page { margin: 0; size: 50mm auto; }
        body { margin: 0; padding: 0; background: #fff; color: #000; font-family: monospace; font-size: 10px; line-height: 1.2; }
        .thermal-receipt { width: 48mm; margin: 0 auto; padding: 1mm; overflow: hidden; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-black { font-weight: 900; }
        .uppercase { text-transform: uppercase; }
        .divider { border-top: 1px dashed #000; margin: 3px 0; }
        .d-flex { display: flex; justify-content: space-between; }
        .mt-1 { margin-top: 3px; }
        .mb-1 { margin-bottom: 3px; }
        .qrcode svg { display: block; max-width: 100%; height: auto; }
        
        @media screen {
            body { background: #f3f4f6; display: flex; justify-content: center; padding: 20px; }
            .thermal-receipt { background: #fff; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        }

        @media print {
            .print\:hidden { display: none !important; }
        }
    </style>

    <div class="thermal-receipt">
        <div class="text-center mb-1">
            <div class="font-black" style="font-size: 14px;">{{ strtoupper($company->name ?? 'KARGO RESMI') }}</div>
            <div style="font-size: 9px;">{{ $company->phone ?? 'Agen' }}</div>
        </div>
        
        <div class="divider"></div>
        
        {{-- Row QR Code & Basic Info (Side by side) --}}
        <div style="display: flex; align-items: center; gap: 8px; margin: 4px 0;">
            <div class="qrcode" style="flex-shrink: 0; width: 64px; height: 64px;">
                {!! QrCode::size(64)->margin(0)->generate(url('/cek-resi?trackingCode=' . $this->cargo->tracking_code)) !!}
            </div>
            <div style="flex-grow: 1;">
                <div class="font-black" style="font-size: 12px; letter-spacing: 0.5px;">
                    {{ $cargo->tracking_code }}
                </div>
                <div style="font-size: 8px; color: #333; margin-bottom: 2px;">
                    {{ $cargo->created_at->format('d/m/y H:i') }}
                </div>
                <div style="font-size: 8px;">
                    <strong>TUJUAN:</strong><br>
                    <span class="font-black" style="font-size: 10px;">{{ strtoupper($cargo->destinationAgent->city ?? '-') }}</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Row PENGIRIM & PENERIMA --}}
        <div style="display: flex; justify-content: space-between; font-size: 9px;">
            <div style="width: 48%;">
                <div class="font-bold">PENGIRIM:</div>
                <div class="font-black" style="line-height:1;">{{ $cargo->booking->booker_name }}</div>
                <div>{{ $cargo->booking->booker_phone ?? '-' }}</div>
            </div>
            <div style="width: 48%; text-align: right;">
                <div class="font-bold">PENERIMA:</div>
                <div class="font-black" style="line-height:1;">{{ $cargo->recipient_name }}</div>
                <div>{{ $cargo->recipient_phone ?? '-' }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Row INFO PAKET --}}
        <div class="d-flex font-black mt-1 mb-1" style="font-size: 10px;">
            <span class="uppercase">{{ $cargo->item_name ?? 'PAKET' }}</span>
            <span>{{ $cargo->weight_kg }}KG | {{ $cargo->quantity }}Koli</span>
        </div>
        @if(!empty($cargo->description))
            <div style="font-size: 8px; margin-bottom: 3px;">{{ $cargo->description }}</div>
        @endif

        <div class="divider"></div>

        {{-- Row TOTAL & LUNAS --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top:2px;">
            <div class="font-black" style="font-size: 11px;">
                TOTAL: Rp{{ number_format($cargo->fee, 0, ',', '.') }}
            </div>
            <div class="font-black" style="font-size: 10px; border: 1.5px solid #000; padding: 1px 3px;">
                {{ $cargo->is_paid ? 'LUNAS' : 'BLM LUNAS' }}
            </div>
        </div>

        <div class="divider mt-1"></div>
        
        <div class="text-center mt-1" style="font-size: 8px;">
            Harap simpan resi ini sebagai <br>bukti pengambilan barang.
        </div>
        
        <div class="text-center mt-2 print:hidden mb-1">
            <button onclick="window.print()" style="padding: 6px 12px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;">
                CETAK SEKARANG
            </button>
            <div style="margin-top: 8px;">
                <a href="{{ route('cargo.show', $cargo) }}" style="color: #666; text-decoration: underline; font-size: 11px;">Kembali ke Detail</a>
            </div>
        </div>
    </div>
</div>
