<?php

use App\Http\Controllers\ExportController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::livewire('/', 'pages::dashboard')->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Route::livewire('pengaturan/akun', 'pengaturan.akun')->name('pengaturan.akun');

    Route::group(['prefix' => '/jadwal'], function () {
        Route::livewire('pelajaran', 'jadwal.periode')
            ->name('jadwal.periode');

        Route::livewire('pelajaran/detail/{periode_id}', 'jadwal.index')
            ->name('jadwal.index');

        Route::livewire('periode', 'jadwal.periode-old')
            ->name('jadwal.periode');
    });

    Route::group(['prefix' => '/data'], function () {
        Route::livewire('mata-pelajaran', 'data.mata-pelajaran')->name('data.mata-pelajaran');
        Route::livewire('guru', 'data.guru')->name('data.guru');
        Route::livewire('kelas', 'data.kelas')->name('data.kelas');
        Route::livewire('kegiatan', 'data.kegiatan')->name('data.kegiatan');
        Route::livewire('jam-pelajaran', 'data.jam-pelajaran')->name('data.jam-pelajaran');
    });

    Route::livewire('atur-admin', 'atur-admin.index')
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
