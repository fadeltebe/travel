<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// ── Redirect root ke login ─────────────────
Route::get('/', function () {
    return redirect()->route('dashboard');
});

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


    // Cargo (placeholder)
    Route::get('/cargo', function () {
        return 'Coming soon';
    })->name('cargo.index');

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
