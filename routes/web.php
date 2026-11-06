<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\DokterController;
use App\Http\Controllers\JadwalDokterController;

Route::resource('dokter', DokterController::class)->except(['show']);

Route::get('/jadwal', [JadwalDokterController::class, 'index'])->name('jadwal.index');
Route::post('/jadwal', [JadwalDokterController::class, 'store'])->name('jadwal.store');http://127.0.0.1:8000/dokterphp artisan serve