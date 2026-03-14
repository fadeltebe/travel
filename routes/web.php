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

    // Cargo (placeholder)
    Route::get('/cargo', function () {
        return 'Coming soon';
    })->name('cargo.index');

    // Profile (bawaan Breeze)
    Volt::route('/profile', 'profile')
        ->name('profile.edit');
});
