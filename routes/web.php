<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// ── Redirect root ke login ─────────────────
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ── Lacak Resi (Publik) ──────────────
Volt::route('/cek-resi', 'cargos.track')
    ->name('cargo.track');

// ── Auth Routes (dari Breeze) ──────────────
require __DIR__ . '/auth.php';

// ── Protected Routes ───────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Volt::route('/dashboard', 'dashboard.index')
        ->name('dashboard');

    // Agents
    Volt::route('/agents', 'agents.index')
        ->name('agents.index');

    // Schedules
    Volt::route('/schedules', 'schedules.index')
        ->name('schedules.index');
    Volt::route('/schedules/create', 'schedules.create')
        ->name('schedules.create');
    Volt::route('/schedules/{schedule}/edit', 'schedules.edit')
        ->name('schedules.edit');
    Volt::route('/schedules/{schedule}', 'schedules.show')
        ->name('schedules.show');

    //Bookings
    // Route untuk Booking Penumpang (Wizard 4 step)
    Volt::route('/bookings/create', 'bookings.createpassenger')
        ->name('bookings.create');

    // Route untuk Kirim Barang (Quick Add Cargo)
    Volt::route('/cargo/create', 'bookings.createcargo')
        ->name('cargo.create');


    Volt::route('/cargo', 'cargos.index')
        ->name('cargo.index');
    Volt::route('/cargo/{cargo}', 'cargos.show')
        ->name('cargo.show');
        
    Route::get('/cargo/{cargo}/print', function (\App\Models\Cargo $cargo) {
        $cargo->load(['booking', 'originAgent', 'destinationAgent']);

        $center = function ($text, $width = 32) {
            $text = trim($text);
            if (strlen($text) >= $width) return $text;
            $padding = (int) floor(($width - strlen($text)) / 2);
            return str_repeat(' ', max(0, $padding)) . $text;
        };

        $bPad = function ($left, $right, $width = 32) {
            $space = $width - strlen($left) - strlen($right);
            if ($space < 1) $space = 1;
            return $left . str_repeat(' ', $space) . $right;
        };

        // Header
        $text = $center("KARGO RESMI") . "\n";
        $text .= $center($cargo->originAgent->city ?? 'Agen') . "\n";
        $text .= str_repeat('-', 32) . "\n";
        
        // Memaksa RawBT untuk memproses tag QRCode (Fitur Native RawBT)
        $trackUrl = url('/cek-resi?trackingCode=' . $cargo->tracking_code);
        $text .= "<qrcode>{$trackUrl}</qrcode>\n";
        
        $text .= $center("Resi: " . $cargo->tracking_code) . "\n";
        $text .= $center($cargo->created_at->format('d/m/Y H:i') . ' WIB') . "\n";
        $text .= str_repeat('-', 32) . "\n";

        // Detail Kompak
        $text .= "PENGIRIM: " . ($cargo->booking->booker_name ?? '-') . " (" . ($cargo->booking->booker_phone ?? '-') . ")\n";
        $text .= "PENERIMA: " . ($cargo->recipient_name ?? '-') . " (" . ($cargo->recipient_phone ?? '-') . ")\n";
        $text .= "TUJUAN: " . strtoupper($cargo->destinationAgent->city ?? '-') . "\n";
        $text .= str_repeat('-', 32) . "\n";

        $text .= strtoupper($cargo->item_name ?? 'PAKET') . "\n";
        if (!empty($cargo->description)) {
            $text .= ($cargo->description) . "\n";
        }
        $text .= "Brt: " . $cargo->weight_kg . " KG | Koli: " . $cargo->quantity . " BOX\n";
        $text .= str_repeat('-', 32) . "\n";

        $text .= $bPad("TOTAL:", "Rp" . number_format($cargo->fee, 0, ',', '.')) . "\n";

        $status = $cargo->is_paid ? 'LUNAS' : 'BELUM LUNAS';
        $text .= $center("[ " . $status . " ]") . "\n";
        
        $text .= str_repeat('-', 32) . "\n";
        $text .= $center("Harap simpan resi ini sebagai") . "\n";
        $text .= $center("bukti pengambilan barang.") . "\n\n\n";

        $encodedText = urlencode($text);
        $intentUrl = "intent:$encodedText#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";

        return redirect()->away($intentUrl);
    })->name('cargo.print');

    // Passengers
    Volt::route('/passengers', 'passengers.index')
        ->name('passengers.index');
    Volt::route('/passengers/{passenger}', 'passengers.show')
        ->name('passengers.show');
    Volt::route('/passengers/{passenger}/print', 'passengers.print')
        ->name('passengers.print');

    // Profile (bawaan Breeze)
    Volt::route('/profile', 'profile')
        ->name('profile.edit');

    // Logout
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
