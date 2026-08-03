<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('login', 'pages::auth.login')->name('login');

});

// Route::middleware('auth')->group(function () {
//     Volt::route('verify-email', 'auth.verify-email')
//         ->name('verification.notice');

//     Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
//         ->middleware(['signed', 'throttle:6,1'])
//         ->name('verification.verify');
// });

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
