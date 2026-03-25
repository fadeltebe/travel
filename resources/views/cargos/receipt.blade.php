<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resi Kargo {{ $cargo->tracking_code }}</title>
    <style>
        body { margin: 0; padding: 0; font-family: monospace; font-size: 11px; line-height: 1.2; color: #000; background: #fff; width: 48mm; }
        .text-center { text-align: center; }
        .font-black { font-weight: 900; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .d-flex { display: flex; justify-content: space-between; }
        .qr-container { text-align: center; margin: 4px 0; }
        .qr-container svg { width: 100px !important; height: 100px !important; margin: 0 auto; display: block; }
    </style>
</head>
<body>

    <div class="text-center">
        <div class="font-black" style="font-size: 14px;">KARGO RESMI</div>
        <div style="font-size: 10px;">{{ $cargo->originAgent->city ?? 'Agen' }}</div>
    </div>

    <div class="divider"></div>

    <div class="qr-container">
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->margin(0)->generate(url('/cek-resi?trackingCode=' . $cargo->tracking_code)) !!}
    </div>
    
    <div class="text-center font-black" style="font-size: 12px; margin-top: 2px;">
        {{ $cargo->tracking_code }}
    </div>
    <div class="text-center" style="font-size: 9px;">
        {{ $cargo->created_at->format('d/m/Y H:i') }} WIB
    </div>

    <div class="divider"></div>

    <div style="font-size: 10px;">
        <span class="font-bold">PENGIRIM:</span> {{ $cargo->booking->booker_name ?? '-' }} ({{ $cargo->booking->booker_phone ?? '-' }})<br>
        <span class="font-bold">PENERIMA:</span> {{ $cargo->recipient_name ?? '-' }} ({{ $cargo->recipient_phone ?? '-' }})<br>
        <span class="font-black">TUJUAN: {{ strtoupper($cargo->destinationAgent->city ?? '-') }}</span>
    </div>

    <div class="divider"></div>

    <div class="font-black" style="font-size: 10px; text-transform: uppercase;">{{ $cargo->item_name ?? 'PAKET' }}</div>
    <div style="font-size: 9px;">{{ $cargo->description }}</div>
    
    <div class="d-flex font-bold" style="font-size: 10px; margin-top: 2px;">
        <span>Brt: {{ $cargo->weight_kg }} KG</span>
        <span>Koli: {{ $cargo->quantity }} BX</span>
    </div>

    <div class="divider"></div>

    <div class="d-flex font-black" style="font-size: 11px;">
        <span>TOTAL:</span>
        <span>Rp{{ number_format($cargo->fee, 0, ',', '.') }}</span>
    </div>
    
    <div class="text-center" style="margin-top: 4px;">
        <span class="font-black" style="font-size: 11px; border: 1.5px solid #000; padding: 1px 4px; display: inline-block;">
            {{ $cargo->is_paid ? 'LUNAS' : 'BELUM LUNAS' }}
        </span>
    </div>

    <div class="divider"></div>
    
    <div class="text-center" style="font-size: 8px;">
        Harap simpan resi ini sebagai <br>bukti pengambilan barang.
    </div>

</body>
</html>
