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

    Volt::route('/billings', 'billings.index')
        ->name('billings.index');

    Volt::route('/billings/{billing}', 'billings.show')
        ->name('billings.show');

    Volt::route('/billings/{billing}/print', 'billings.print')
        ->name('billings.print');

    Volt::route('/billings/{billing}/pay', 'billings.pay')
        ->name('billings.pay');

    Volt::route('/billings/{billing}/refund', 'billings.refund')
        ->name('billings.refund');

    Volt::route('/billings/{billing}/cancel', 'billings.cancel')
        ->name('billings.cancel');

    Volt::route('/billings/{billing}/resend', 'billings.resend')
        ->name('billings.resend');

    Volt::route('/billings/{billing}/void', 'billings.void')
        ->name('billings.void');

    Volt::route('/billings/{billing}/capture', 'billings.capture')
        ->name('billings.capture');

    Volt::route('/settings', 'settings.index')
        ->name('settings.index');

    // Reports
    Volt::route('/reports', 'reports.index')
        ->name('reports.index');

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
