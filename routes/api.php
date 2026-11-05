<?php

use App\Http\Controllers\KlinikCabangController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    // Khusus admin
    Route::middleware(['role:admin'])->group(function () {

        Route::apiResource(
            'klinik-cabang',
            KlinikCabangController::class
        );

        Route::apiResource(
            'polis',
            PoliController::class
        );

        Route::apiResource(
            'doctors',
            DoctorController::class
        );

        Route::apiResource(
            'doctor-schedules',
            DoctorScheduleController::class
        );
    });

});