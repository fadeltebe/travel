<?php

use function Livewire\Volt\{mount, layout};
use App\Models\Cargo;

layout('layouts.blank');

mount(function (Cargo $cargo) {
    $cargo->load(['booking', 'originAgent', 'destinationAgent']);

    $centerText = function ($text, $width = 32) {
        $text = trim($text);
        if (strlen($text) >= $width) return $text;
        $padding = floor(($width - strlen($text)) / 2);
        return str_repeat(' ', max(0, $padding)) . $text;
    };

    $bPad = function ($left, $right, $width = 32) {
        $space = $width - strlen($left) - strlen($right);
        if ($space < 1) $space = 1;
        return $left . str_repeat(' ', $space) . $right;
    };

    $text = $centerText("KARGO RESMI") . "\n";
    $text .= $centerText($cargo->originAgent->city ?? 'Agen') . "\n";
    $text .= str_repeat('-', 32) . "\n";
    
    $text .= $centerText("Resi: " . $cargo->tracking_code) . "\n";
    $text .= $centerText($cargo->created_at->format('d/m/Y H:i') . ' WIB') . "\n";
    $text .= str_repeat('-', 32) . "\n";

    $text .= "PENGIRIM:\n";
    $text .= ($cargo->booking->booker_name ?? '-') . "\n";
    $text .= ($cargo->booking->booker_phone ?? '-') . "\n";
    $text .= "\n";
    
    $text .= "PENERIMA:\n";
    $text .= ($cargo->recipient_name ?? '-') . "\n";
    $text .= ($cargo->recipient_phone ?? '-') . "\n";
    $text .= "TUJUAN: " . strtoupper($cargo->destinationAgent->city ?? '-') . "\n";
    $text .= str_repeat('-', 32) . "\n";

    $text .= strtoupper($cargo->item_name ?? 'PAKET') . "\n";
    $text .= ($cargo->description ?? '-') . "\n";
    $text .= $bPad("Berat:", $cargo->weight_kg . " KG") . "\n";
    $text .= $bPad("Koli:", $cargo->quantity . " BOX") . "\n";
    $text .= str_repeat('-', 32) . "\n";

    $text .= $bPad("TOTAL:", "Rp" . number_format($cargo->fee, 0, ',', '.')) . "\n\n";

    $status = $cargo->is_paid ? 'LUNAS' : 'BELUM LUNAS';
    $text .= $centerText("[ " . $status . " ]") . "\n";
    
    $text .= str_repeat('-', 32) . "\n";
    $text .= $centerText("Harap simpan resi ini sebagai") . "\n";
    $text .= $centerText("bukti pengambilan barang.") . "\n";
    $text .= "\n\n\n"; // Feed kertas

    $encodedText = urlencode($text);
    $intentUrl = "intent:$encodedText#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";

    return redirect()->away($intentUrl);
});
?>
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="text-center space-y-4">
        <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="font-bold text-gray-700">Meneruskan ke Aplikasi Printer RawBT...</p>
        <p class="text-xs text-gray-500">Jika aplikasi tidak terbuka otomatis, pastikan RawBT Print sudah terinstall.</p>
        
        <div class="mt-8">
            <a href="{{ route('cargo.show', request()->route('cargo')) }}" class="text-sm text-blue-600 underline">Kembali ke Detail Kargo</a>
        </div>
    </div>
</div>
