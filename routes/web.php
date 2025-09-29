<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Route::group(['prefix' => '/jadwal'], function() {
        Volt::route('smp', 'jadwal.smp')->name('jadwal.smp');
        Volt::route('ma', 'jadwal.ma')->name('jadwal.ma');
    });


    Route::group(['prefix' => '/data'], function() {
        Volt::route('mata-pelajaran', 'data.mata-pelajaran')->name('data.mata-pelajaran');
        Volt::route('guru', 'data.guru')->name('data.guru');
        Volt::route('kelas', 'data.kelas')->name('data.kelas');
    });

    // Volt::route('settings/two-factor', 'settings.two-factor')
    //     ->middleware(
    //         when(
    //             Features::canManageTwoFactorAuthentication()
    //                 && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
    //             ['password.confirm'],
    //             [],
    //         ),
    //     )
    //     ->name('two-factor.show');
});

require __DIR__.'/auth.php';
