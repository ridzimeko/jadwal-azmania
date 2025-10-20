<?php

namespace App\Providers;

use App\Helpers\JadwalHelper;
use Carbon\Carbon;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Helper global jadwal_available()
        if (!function_exists('jadwal_available')) {
            function jadwal_available($data, $ignoreId = null) {
                return JadwalHelper::isAvailable($data, $ignoreId);
            }
        }

        if (!function_exists('jadwal_has_bentrok')) {
            function jadwal_has_bentrok() {
                return JadwalHelper::hasBentrok();
            }
        }

        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);

        // Set locale Carbon ke Bahasa Indonesia
        Carbon::setLocale('id');
    }
}
