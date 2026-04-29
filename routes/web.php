<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

foreach (config('tenancy.central_domains', []) as $centralDomain) {
    Route::domain($centralDomain)->middleware('web')->group(function () {
        Volt::route('/', 'central.index')->name('central.home');

        Route::middleware('guest')->group(function () {
            Volt::route('/login', 'central.auth.login')
                ->name('central.login');
        });

        Route::middleware('auth')->group(function () {
            Volt::route('/dashboard', 'central.dashboard.index')
                ->name('central.dashboard');

            Volt::route('/tenants/create', 'central.tenants.create')
                ->name('central.tenants.create');
        });
    });
}

Route::get('/', function () {
    if (in_array(request()->getHost(), config('tenancy.central_domains', []))) {
        return redirect()->route('central.home');
    }

    return redirect()->route('dashboard');
});
