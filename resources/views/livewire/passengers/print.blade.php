<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Passenger;
use App\Models\Company;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

layout('layouts.blank');

state(['passenger' => null, 'company' => null]);

mount(function (Passenger $passenger) {
    // Memastikan relasi termuat
    $this->passenger = $passenger->load(['booking.schedule.route.originAgent', 'booking.schedule.route.destinationAgent', 'booking.schedule.bus']);
    $this->company = Company::first();
});

// Menghasilkan QR Code dalam bentuk Base64 PNG agar aman dirender oleh html2canvas
$getQrCodeBase64 = function() {
    try {
        $url = route('passengers.show', $this->passenger->id);
        $qr = QrCode::format('png')->size(150)->margin(0)->generate($url);
        return 'data:image/png;base64,' . base64_encode($qr);
    } catch (\Exception $e) {
        // Fallback
        $url = route('passengers.show', $this->passenger->id);
        return 'data:image/svg+xml;base64,' . base64_encode(QrCode::size(150)->margin(0)->generate($url));
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
            width: 384px; 
            margin: 0 auto; 
            padding: 8px; 
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
        .qrcode img { display: block; max-width: 100%; height: auto; margin: 0 auto; }
        
        @media print {
            @page { margin: 0; size: auto; }
            html, body {
                margin: 0; padding: 0; background: #fff; width: 100%; max-width: 58mm; height: auto !important; overflow: visible !important;
            }
            .thermal-receipt { 
                box-shadow: none; width: 100%; max-width: 58mm; padding: 0; margin: 0;
            }
            .print\:hidden { display: none !important; }
        }
    </style>

    <div id="receipt-capture" class="thermal-receipt">
        <div class="text-center mb-1">
            <div class="font-black" style="font-size: 18px;">{{ strtoupper($company->name ?? 'TIKET TRAVEL') }}</div>
            <div style="font-size: 11px;">{{ $company->phone ?? 'Layanan Travel Official' }}</div>
        </div>
        
        <div class="divider"></div>
        
        <div class="text-center" style="margin: 8px 0;">
            <div class="font-black mb-1" style="font-size: 14px;">E-TIKET PENUMPANG</div>
            <div class="qrcode" style="width: 100px; height: 100px; margin: 0 auto;">
                <img src="{{ $this->getQrCodeBase64() }}" alt="QR Code Tiket">
            </div>
            <div class="font-black mt-1" style="font-size: 16px; letter-spacing: 1px;">
                {{ $passenger->booking->booking_code }}
            </div>
        </div>

        <div class="text-center" style="font-size: 10px; margin-bottom: 4px;">
            Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
        </div>

        <div class="divider"></div>

        <div class="text-center mb-1 mt-1">
            <div class="font-bold" style="font-size: 15px;">{{ strtoupper($passenger->name) }}</div>
            <div>{{ $passenger->phone ?? '-' }}</div>
        </div>

        <div class="divider"></div>

        <div class="font-black mt-1 mb-1" style="font-size: 13px;">RUTE PERJALANAN</div>
        <div class="d-flex mt-1">
            <span class="font-bold">Dari:</span>
            <span>{{ strtoupper($passenger->booking->schedule->route->originAgent->city ?? '-') }}</span>
        </div>
        <div class="d-flex mt-1">
            <span class="font-bold">Tujuan:</span>
            <span>{{ strtoupper($passenger->booking->schedule->route->destinationAgent->city ?? '-') }}</span>
        </div>
        
        <div class="d-flex mt-1">
            <span class="font-bold">Waktu:</span>
            <div class="text-right font-black">
                <div>{{ \Carbon\Carbon::parse($passenger->booking->schedule->departure_time)->format('d M Y') }}</div>
                <div>{{ \Carbon\Carbon::parse($passenger->booking->schedule->departure_time)->format('H:i') }} WIB</div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="d-flex mt-1 align-items-center">
            <span class="font-bold">Armada:</span>
            <span class="font-black">{{ strtoupper($passenger->booking->schedule->bus->name ?? '-') }}</span>
        </div>
        
        <div class="text-center mt-2 mb-2">
            <div class="font-black" style="font-size:24px; border: 2px solid #000; padding: 4px 16px; display: inline-block;">
                KURSI: {{ $passenger->seat_number ?? 'N/A' }}
            </div>
        </div>

        <div class="text-center mb-1 mt-1">
            <div class="font-black" style="font-size:14px; display: inline-block; padding: 2px 4px; border: 1px dashed #000;">
                {{ $passenger->booking->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
            </div>
        </div>

        <div class="divider mt-1" style="margin-top: 6px;"></div>
        
        <div class="text-center mt-1" style="font-size: 10px;">
            Harap hadir 30 menit sebelum <br>jadwal keberangkatan.
        </div>
    </div>

    {{-- TOMBOL AKSI UTILITIES --}}
    <div class="w-[384px] print:hidden mt-6 space-y-3" style="width: 384px; display: flex; flex-direction: column; gap: 10px;">
        
        <div id="loading-indicator" style="display: none; text-align: center; font-weight: bold; color: #666; font-size: 12px; padding: 10px;">
            Memproses gambar tiket... Mohon tunggu...
        </div>

        <div style="display: flex; gap: 8px;">
            <button onclick="cetakRawBT()" style="flex: 1; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <svg class="w-5 h-5" style="width: 18px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Cetak Gambar (RawBT)
            </button>
            <button onclick="shareWA()" style="flex: 1; padding: 12px; background: #16a34a; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                 <svg class="w-5 h-5" style="width: 18px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                </svg>
                Kirim via WA
            </button>
        </div>

        <button onclick="window.print()" style="padding: 12px; background: #1f2937; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 13px; width: 100%;">
            Cetak Standar (Browser)
        </button>

        <div style="text-align: center; margin-top: 12px;">
            <a href="{{ route('passengers.show', $passenger) }}" style="color: #4b5563; text-decoration: underline; font-size: 12px; font-family: sans-serif;">&larr; Kembali ke Detail Penumpang</a>
        </div>
    </div>

    <script>
        async function generateReceiptImage() {
            document.getElementById('loading-indicator').style.display = 'block';
            const element = document.getElementById('receipt-capture');
            
            try {
                const canvas = await html2canvas(element, { 
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    width: element.offsetWidth,
                    height: element.offsetHeight,
                    windowWidth: element.offsetWidth,
                    windowHeight: element.offsetHeight
                });
                document.getElementById('loading-indicator').style.display = 'none';
                return canvas;
            } catch (err) {
                console.error(err);
                document.getElementById('loading-indicator').style.display = 'none';
                alert('Gagal menghasilkan gambar tiket.');
                return null;
            }
        }

        async function cetakRawBT() {
            const canvas = await generateReceiptImage();
            if (!canvas) return;
            const base64 = canvas.toDataURL("image/png");
            // Menghilangkan awalan data:image/png;base64, agar intent URL benar
            const cleanBase64 = base64.replace("data:image/png;base64,", "");
            const intentUrl = "intent:" + cleanBase64 + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
            window.location.href = intentUrl;
        }

        async function shareWA() {
            const canvas = await generateReceiptImage();
            if (!canvas) return;

            canvas.toBlob(async function(blob) {
                const file = new File([blob], "tiket-penumpang.png", { type: "image/png" });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({
                            files: [file],
                            title: 'Tiket Penumpang - {{ $company->name ?? "Travel Official" }}',
                            text: 'Berikut adalah e-tiket keberangkatan Anda.'
                        });
                    } catch (error) {
                        console.log('User membatalkan share atau error: ', error);
                    }
                } else {
                    alert("Browser Anda tidak mendukung fitur berbagi gambar secara langsung. Harap simpan gambar manual.");
                    const imgUrl = canvas.toDataURL("image/png");
                    const w = window.open('about:blank', '_blank');
                    w.document.write('<img src="'+imgUrl+'" style="max-width:100%;"/>');
                }
            }, 'image/png');
        }
    </script>
</div>
