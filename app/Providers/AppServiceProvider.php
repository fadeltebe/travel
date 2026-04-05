<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }


        // Memaksa seluruh tanggal di aplikasi menggunakan bahasa Indonesia
        Carbon::setLocale('id');

        // 1. Format: 29 Maret 2026
        Carbon::macro('toIndoDate', function () {
            return $this->translatedFormat('d F Y');
        });

        // 2. Format: Minggu, 29 Maret 2026
        Carbon::macro('toIndoDayDate', function () {
            return $this->translatedFormat('l, d F Y');
        });

        // 3. Format: 29 Mar 2026 - 14:30
        Carbon::macro('toIndoDateTime', function () {
            return $this->translatedFormat('d M Y - H:i');
        });
    }
}
