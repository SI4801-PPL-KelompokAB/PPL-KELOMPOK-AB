
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DokterKonsultasiController;
use App\Http\Controllers\KonsultasiOnlineController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RuangKonsultasiController;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// PBI Registrasi Pasien dan Login User
Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.store');

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.authenticate');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// PBI07: Admin - Kelola Jadwal Praktik Dokter
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('jadwal', DoctorScheduleController::class)
        ->parameters(['jadwal' => 'doctorSchedule'])
        ->except(['show']);
    Route::post('jadwal/{doctorSchedule}/toggle-status', [DoctorScheduleController::class, 'toggleStatus'])
        ->name('jadwal.toggle-status');
});

// PBI09: Pasien - Antrean Online
Route::middleware(['auth', 'role:pasien'])->prefix('antrean')->name('antrean.')->group(function () {
    Route::get('/', [QueueController::class, 'index'])->name('index');
    Route::get('/ambil', [QueueController::class, 'create'])->name('create');
    Route::post('/', [QueueController::class, 'store'])->name('store');
    Route::get('/{queue}', [QueueController::class, 'show'])->name('show');
});

// PBI10: Pasien
Route::prefix('konsultasi')
    ->name('konsultasi.')
    ->group(function () {
        Route::get('/buat', [KonsultasiOnlineController::class, 'create'])
            ->name('create');

        Route::post('/', [KonsultasiOnlineController::class, 'store'])
            ->name('store');

        Route::get('/{consultation}', [KonsultasiOnlineController::class, 'show'])
            ->name('show');

        Route::get('/{consultation}/ruang', [RuangKonsultasiController::class, 'show'])
            ->name('ruang');

        Route::post('/{consultation}/pesan', [RuangKonsultasiController::class, 'kirim'])
            ->name('pesan');
    });

// PBI11: Dokter
Route::prefix('dokter/konsultasi')
    ->name('dokter.konsultasi.')
    ->group(function () {
        Route::get('/', [DokterKonsultasiController::class, 'index'])
            ->name('index');

        Route::post('/{consultation}/mulai', [DokterKonsultasiController::class, 'mulai'])
            ->name('mulai');

        Route::post('/{consultation}/selesai', [DokterKonsultasiController::class, 'selesai'])
            ->name('selesai');
    });
