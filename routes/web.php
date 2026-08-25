<?php

use App\Http\Controllers\ExportController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::livewire('/', 'pages::dashboard')->name('dashboard');
    Route::livewire('/jadwal-lihat', 'pages::jadwal.view-only')->name('jadwal.view-only');

    Route::redirect('settings', 'pages::settings/profile');

    Route::livewire('pengaturan/akun', 'pages::pengaturan.akun')->name('pengaturan.akun');

    Route::group(['prefix' => '/jadwal'], function () {
        Route::livewire('pelajaran', 'pages::jadwal.periode')
            ->name('jadwal.pelajaran');

        Route::livewire('pelajaran/detail/{periode_id}', 'pages::jadwal.index')
            ->name('jadwal.index');

        Route::livewire('periode', 'pages::jadwal.periode')
            ->name('jadwal.periode');
    });

    Route::group(['prefix' => '/data'], function () {
        Route::livewire('mata-pelajaran', 'pages::data.mata-pelajaran')->name('data.mata-pelajaran');
        Route::livewire('guru', 'pages::data.guru')->name('data.guru');
        Route::livewire('kelas', 'pages::data.kelas')->name('data.kelas');
        Route::livewire('kegiatan', 'pages::data.kegiatan')->name('data.kegiatan');
        Route::livewire('jam-pelajaran', 'pages::data.jam-pelajaran')->name('data.jam-pelajaran');
    });

    Route::livewire('atur-admin', 'pages::atur-admin.index')
        ->name('atur-admin')
        ->middleware(RoleMiddleware::class . ':superadmin');

    Route::livewire('log-aktivitas', 'pages::log-aktivitas.index')
        ->name('log-aktivitas');

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

    // Route::livewire('settings/two-factor', 'settings.two-factor')
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

require __DIR__ . '/auth.php';
