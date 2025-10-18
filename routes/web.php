<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Middleware\RoleMiddleware;
use App\Livewire\PdfExport;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Volt::route('pengaturan/akun', 'pengaturan.akun')->name('pengaturan.akun');
    // Volt::route('settings/password', 'settings.password')->name('password.edit');
    // Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Route::group(['prefix' => '/jadwal'], function() {
        Volt::route('{tingkat}', 'jadwal.index')
            ->where('tingkat', 'smp|ma')
            ->name('jadwal.index');

        Volt::route('{tingkat}/edit', 'jadwal.edit')
            ->where('tingkat', 'smp|ma')
            ->name('jadwal.edit');
    });

    Route::group(['prefix' => '/data'], function() {
        Volt::route('mata-pelajaran', 'data.mata-pelajaran')->name('data.mata-pelajaran');
        Volt::route('guru', 'data.guru')->name('data.guru');
        Volt::route('kelas', 'data.kelas')->name('data.kelas');
    });

    Volt::route('atur-admin', 'atur-admin.index')
    ->name('atur-admin')
    ->middleware(RoleMiddleware::class . ':superadmin');

    Route::get('/download/template/{type}', function ($type) {
        $filename = "template_{$type}.xlsx";
        $path = public_path("templates/{$filename}");

        if (!file_exists($path)) {
            abort(404, 'Template tidak ditemukan.');
        }

        return response()->download($path, $filename);
    })->name('download.template');

    Route::get('/export/jadwal/pdf', [ExportController::class, 'exportPdf'])->name('export-jadwal.pdf');
    Route::get('/export/jadwal/excel', [ExportController::class, 'exportExcel'])->name('export-jadwal.excel');

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
