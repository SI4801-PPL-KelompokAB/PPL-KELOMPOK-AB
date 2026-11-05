<?php

use App\Http\Controllers\DokterKonsultasiController;
use App\Http\Controllers\KonsultasiOnlineController;
use App\Http\Controllers\RuangKonsultasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// PBI10: pasien
Route::prefix('konsultasi')
    ->name('konsultasi.')
    ->group(function () {
        Route::get('/buat', [KonsultasiOnlineController::class, 'create'])->name('create');
        Route::post('/', [KonsultasiOnlineController::class, 'store'])->name('store');
        Route::get('/{consultation}', [KonsultasiOnlineController::class, 'show'])->name('show');

        Route::get('/{consultation}/ruang', [RuangKonsultasiController::class, 'show'])->name('ruang');
        Route::post('/{consultation}/pesan', [RuangKonsultasiController::class, 'kirim'])->name('pesan');
    });

// PBI11: dokter
Route::prefix('dokter/konsultasi')
    ->name('dokter.konsultasi.')
    ->group(function () {
        Route::get('/', [DokterKonsultasiController::class, 'index'])->name('index');
        Route::post('/{consultation}/mulai', [DokterKonsultasiController::class, 'mulai'])->name('mulai');
        Route::post('/{consultation}/selesai', [DokterKonsultasiController::class, 'selesai'])->name('selesai');
    });