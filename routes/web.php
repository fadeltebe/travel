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

// ── Webhook Midtrans ───────────
Route::post('/midtrans/webhook', [\App\Http\Controllers\MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook');

// ── Auth Routes (dari Breeze) ──────────────
require __DIR__ . '/auth.php';

// ── Protected Routes ───────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Volt::route('/dashboard', 'dashboard.index')
        ->name('dashboard');

    // Wallets / Billing
    Volt::route('/wallets', 'wallets.index')
        ->name('wallets.index');

    Volt::route('/wallets/topup', 'wallets.topup')
        ->name('wallets.topup');

    Volt::route('/billings', 'billings.index')
        ->name('billings.index');

    Volt::route('/billings/{billing}', 'billings.show')
        ->name('billings.show');

    Volt::route('/settings', 'settings.index')
        ->name('settings.index');

    Volt::route('/settings/company', 'settings.company')
        ->name('settings.company');

    // Reports
    Volt::route('/reports', 'reports.index')
        ->name('reports.index');

    // Agents
    Volt::route('/agents', 'agents.index')
        ->name('agents.index');
        
    Volt::route('/agents/monitoring', 'agents.monitoring')
        ->name('agents.monitoring');

    // Schedules
    Volt::route('/schedules', 'schedules.index')
        ->name('schedules.index');
    Volt::route('/schedules/create', 'schedules.create')
        ->name('schedules.create');
    Volt::route('/schedules/{schedule}/edit', 'schedules.edit')
        ->name('schedules.edit');
    Volt::route('/schedules/{schedule}', 'schedules.show')
        ->name('schedules.show');
    Volt::route('/schedules/{schedule}/manifest', 'schedules.manifest')
        ->name('schedules.manifest');

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
    Volt::route('/cargo/{cargo}/print', 'cargos.print')
        ->name('cargo.print');

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
