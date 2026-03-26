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

// Menghasilkan QR Code dalam bentuk Base64 PNG agar aman dirender oleh html2canvas
$getQrCodeBase64 = function() {
    try {
        $qr = QrCode::format('png')->size(150)->margin(0)->generate(url('/cek-resi?trackingCode=' . $this->cargo->tracking_code));
        return 'data:image/png;base64,' . base64_encode($qr);
    } catch (\Exception $e) {
        // Fallback jika imagick/gd tidak terinstall, render SVG text biasa
        return 'data:image/svg+xml;base64,' . base64_encode(QrCode::size(150)->margin(0)->generate(url('/cek-resi?trackingCode=' . $this->cargo->tracking_code)));
    }
};

?>

<div>
    {{-- Memuat html2canvas --}}
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        /* Desain dioptimalkan untuk ukuran pixel pasti setara thermal 58mm (384px width) */
        @page { margin: 0; }
        body { margin: 0; padding: 20px 10px; background: #f3f4f6; display: flex; flex-direction: column; align-items: center; color: #000; font-family: monospace; font-size: 13px; line-height: 1.2; }
        
        .thermal-receipt { 
            width: 384px; /* Lebar piksel persis printer thermal 58mm/50mm */
            margin: 0 auto; 
            padding: 8px; /* Padding dalam kertas */
            background: #ffffff; 
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); 
            box-sizing: border-box;
        }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-black { font-weight: 900; }
        .uppercase { text-transform: uppercase; }
        .divider { border-top: 2px dashed #000; margin: 6px 0; }
        .d-flex { display: flex; justify-content: space-between; }
        .mt-1 { margin-top: 4px; }
        .mb-1 { margin-bottom: 4px; }
        .qrcode img { display: block; max-width: 100%; height: auto; }
        
        /* Hilangkan box shadow saat benar-benar mencetak */
        @media print {
            body { padding: 0; background: #fff; }
            .thermal-receipt { box-shadow: none; width: 100%; max-width: 48mm; padding: 0; }
            .print\:hidden { display: none !important; }
        }
    </style>

    {{-- KONTEN STRUK --}}
    <div id="receipt-capture" class="thermal-receipt">
        <div class="text-center mb-1">
            <div class="font-black" style="font-size: 18px;">{{ strtoupper($company->name ?? 'KARGO RESMI') }}</div>
            <div style="font-size: 11px;">{{ $company->phone ?? 'Agen' }}</div>
        </div>
        
        <div class="divider"></div>
        
        {{-- Row QR Code & Basic Info (Side by side) --}}
        <div style="display: flex; align-items: center; gap: 12px; margin: 8px 0;">
            <div class="qrcode" style="flex-shrink: 0; width: 84px; height: 84px;">
                <img src="{{ $this->getQrCodeBase64() }}" alt="QR Code Resi">
            </div>
            <div style="flex-grow: 1;">
                <div class="font-black" style="font-size: 16px; letter-spacing: 0.5px;">
                    {{ $cargo->tracking_code }}
                </div>
                <div style="font-size: 10px; color: #333; margin-bottom: 4px;">
                    {{ $cargo->created_at->format('d/m/y H:i') }} WIB
                </div>
                <div style="font-size: 10px;">
                    <strong>TUJUAN:</strong><br>
                    <span class="font-black" style="font-size: 13px;">{{ strtoupper($cargo->destinationAgent->city ?? '-') }}</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Row PENGIRIM & PENERIMA --}}
        <div style="display: flex; justify-content: space-between; font-size: 11px;">
            <div style="width: 48%;">
                <div class="font-bold">PENGIRIM:</div>
                <div class="font-black" style="line-height:1.1;">{{ $cargo->booking->booker_name }}</div>
                <div>{{ $cargo->booking->booker_phone ?? '-' }}</div>
            </div>
            <div style="width: 48%; text-align: right;">
                <div class="font-bold">PENERIMA:</div>
                <div class="font-black" style="line-height:1.1;">{{ $cargo->recipient_name }}</div>
                <div>{{ $cargo->recipient_phone ?? '-' }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Row INFO PAKET --}}
        <div class="d-flex font-black mt-1 mb-1" style="font-size: 13px;">
            <span class="uppercase">{{ $cargo->item_name ?? 'PAKET' }}</span>
            <span>{{ $cargo->weight_kg }}KG | {{ $cargo->quantity }}Koli</span>
        </div>
        @if(!empty($cargo->description))
            <div style="font-size: 11px; margin-bottom: 4px;">{{ $cargo->description }}</div>
        @endif

        <div class="divider"></div>

        {{-- Row TOTAL & LUNAS --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top:4px;">
            <div class="font-black" style="font-size: 14px;">
                TOTAL: Rp{{ number_format($cargo->fee, 0, ',', '.') }}
            </div>
            <div class="font-black" style="font-size: 12px; border: 2px solid #000; padding: 2px 4px;">
                {{ $cargo->is_paid ? 'LUNAS' : 'BLM LUNAS' }}
            </div>
        </div>

        <div class="divider mt-1" style="margin-top: 6px;"></div>
        
        <div class="text-center mt-1" style="font-size: 10px;">
            Harap simpan resi ini sebagai <br>bukti pengambilan barang.
        </div>
    </div>
    {{-- END KONTEN STRUK --}}

    {{-- TOMBOL AKSI UTILITIES --}}
    <div class="w-[384px] print:hidden mt-6 space-y-3" style="width: 384px; display: flex; flex-direction: column; gap: 10px;">
        
        <div id="loading-indicator" style="display: none; text-align: center; font-weight: bold; color: #666; font-size: 12px; padding: 10px;">
            Memproses gambar struk... Mohon tunggu...
        </div>

        <div style="display: flex; gap: 8px;">
            <button onclick="cetakRawBT()" style="flex: 1; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <x-heroicon-s-printer class="w-5 h-5" style="width: 18px;" />
                Cetak Gambar (RawBT)
            </button>
            <button onclick="shareWA()" style="flex: 1; padding: 12px; background: #16a34a; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <x-heroicon-s-share class="w-5 h-5" style="width: 18px;" />
                Kirim via WA
            </button>
        </div>

        <button onclick="window.print()" style="padding: 12px; background: #1f2937; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 13px; width: 100%;">
            Cetak Standar (Browser)
        </button>

        <div style="text-align: center; margin-top: 12px;">
            <a href="{{ route('cargo.show', $cargo) }}" style="color: #4b5563; text-decoration: underline; font-size: 12px; font-family: sans-serif;">&larr; Kembali ke Detail Kargo</a>
        </div>
    </div>

    {{-- SCRIPTS LOGIC UNTUK CANVAS --}}
    <script>
        async function generateReceiptImage() {
            document.getElementById('loading-indicator').style.display = 'block';
            const element = document.getElementById('receipt-capture');
            
            try {
                // Menentukan scale 2 supaya gambar tidak pecah saat dicetak
                const canvas = await html2canvas(element, { 
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                });
                document.getElementById('loading-indicator').style.display = 'none';
                return canvas;
            } catch (err) {
                console.error(err);
                document.getElementById('loading-indicator').style.display = 'none';
                alert('Gagal menghasilkan gambar struk.');
                return null;
            }
        }

        async function cetakRawBT() {
            const canvas = await generateReceiptImage();
            if (!canvas) return;

            // RawBT menerima intent base64 gambar
            const base64 = canvas.toDataURL("image/png");
            
            // Format Intent RawBT untuk gambar 
            const intentUrl = "intent:" + base64 + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
            
            // Eksekusi redirect ke aplikasi RawBT
            window.location.href = intentUrl;
        }

        async function shareWA() {
            const canvas = await generateReceiptImage();
            if (!canvas) return;

            canvas.toBlob(async function(blob) {
                const file = new File([blob], "resi-kargo.png", { type: "image/png" });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({
                            files: [file],
                            title: 'Resi Kargo - {{ $company->name ?? "Kargo Pilihan" }}',
                            text: 'Berikut adalah resi pengiriman kargo Anda.'
                        });
                    } catch (error) {
                        console.log('User membatalkan share atau error: ', error);
                    }
                } else {
                    alert("Browser Anda tidak mendukung fitur berbagi gambar secara langsung. Harap simpan / tahan gambar manual.");
                    // Fallback jika tidak dukung: Buka gambar di tap baru
                    const imgUrl = canvas.toDataURL("image/png");
                    const w = window.open('about:blank', '_blank');
                    w.document.write('<img src="'+imgUrl+'" style="max-width:100%;"/>');
                }
            }, 'image/png');
        }
    </script>
</div>
