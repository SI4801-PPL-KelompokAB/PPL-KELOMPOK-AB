<?php

use App\Http\Controllers\Admin\PoliController;
use Illuminate\Support\Facades\Route;

// PBI-05: Mengelola Data Poli
Route::resource('poli', PoliController::class)->except(['show', 'destroy']);
Route::patch('poli/{poli}/toggle-status', [PoliController::class, 'toggleStatus'])
    ->name('poli.toggle-status');
