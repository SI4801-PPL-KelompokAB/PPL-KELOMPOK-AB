<?php

use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\PoliController;
use Illuminate\Support\Facades\Route;

// PBI-05: Mengelola Data Poli
Route::resource('poli', PoliController::class)->except(['show', 'destroy']);
Route::patch('poli/{poli}/toggle-status', [PoliController::class, 'toggleStatus'])
    ->name('poli.toggle-status');

// PBI-12: Mengelola Layanan dan Tarif Klinik
Route::resource('layanan', LayananController::class)->except(['show', 'destroy']);
Route::patch('layanan/{layanan}/toggle-status', [LayananController::class, 'toggleStatus'])
    ->name('layanan.toggle-status');
