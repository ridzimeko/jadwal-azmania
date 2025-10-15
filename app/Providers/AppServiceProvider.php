<?php

namespace App\Providers;

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
        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);

        // Set locale Carbon ke Bahasa Indonesia
        Carbon::setLocale('id');
    }
}
