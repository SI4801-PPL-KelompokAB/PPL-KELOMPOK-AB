<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminQueueController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\PoliController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DokterKonsultasiController;
use App\Http\Controllers\KonsultasiOnlineController;
use App\Http\Controllers\Patient\PatientDashboardController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RuangKonsultasiController;
use App\Http\Controllers\UserManagementController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman utama -> Arahkan ke Dashboard (jika sudah login) atau Halaman Login Bonamu
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ──────────────────────────────────────────
// PBI-01 & PBI-02: Registrasi Pasien & Login User
// ──────────────────────────────────────────
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ──────────────────────────────────────────
// Multi-role Dashboard Router
// ──────────────────────────────────────────
Route::get('/dashboard', function () {
    $role = Auth::user()?->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'pasien' => redirect()->route('pasien.dashboard'),
        'dokter' => redirect()->route('dokter.konsultasi.index'),
        default => redirect('/login'),
    };
})->middleware('auth')->name('dashboard');

// ──────────────────────────────────────────
// Route Cepat untuk Pengujian / White Box Testing
// ──────────────────────────────────────────
Route::get('/dev/login-as/{role}', function (string $role) {
    $email = match ($role) {
        'admin' => 'admin@test.com',
        'dokter' => 'dokter.andi@test.com',
        'pasien' => 'pasien@test.com',
        default => null,
    };

    $user = User::where('email', $email)->orWhere('role', $role)->first();

    if ($user) {
        Auth::login($user);

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard')->with('sukses', "Login instan berhasil sebagai Admin ({$user->name})"),
            'dokter' => redirect()->route('dokter.konsultasi.index')->with('success', "Login instan berhasil sebagai Dokter ({$user->name})"),
            'pasien' => redirect()->route('pasien.dashboard')->with('success', "Login instan berhasil sebagai Pasien ({$user->name})"),
            default => redirect('/'),
        };
    }

    return redirect('/login')->withErrors(['error' => 'Akun uji tidak ditemukan. Silakan jalankan seeder terlebih dahulu.']);
})->name('dev.login-as');

// ──────────────────────────────────────────
// Admin Routes (Dashboard, Antrean, Poli, Cabang, Jadwal, Profil)
// ──────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Kelola Antrean Pasien
    Route::get('/antrean', [AdminQueueController::class, 'index'])->name('antrean.index');
    Route::patch('/antrean/{queue}/status', [AdminQueueController::class, 'updateStatus'])->name('antrean.update-status');

    // Profil Admin
    Route::get('/profil', [AdminProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [AdminProfileController::class, 'update'])->name('profil.update');

    // PBI-05: Mengelola Data Poli
    Route::resource('poli', PoliController::class)->except(['show', 'destroy']);
    Route::patch('poli/{poli}/toggle-status', [PoliController::class, 'toggleStatus'])->name('poli.toggle-status');

    // PBI-12: Mengelola Master Data Layanan dan Tarif
    Route::resource('layanan', LayananController::class)->except(['show', 'destroy']);
    Route::patch('layanan/{layanan}/toggle-status', [LayananController::class, 'toggleStatus'])->name('layanan.toggle-status');

    // PBI-07: Kelola Jadwal Praktik Dokter
    Route::resource('jadwal', DoctorScheduleController::class)
        ->parameters(['jadwal' => 'doctorSchedule'])
        ->except(['show']);
    Route::post('jadwal/{doctorSchedule}/toggle-status', [DoctorScheduleController::class, 'toggleStatus'])
        ->name('jadwal.toggle-status');
});

// PBI-03: Admin membuat akun dokter dan admin (otorisasi internal di controller)
Route::prefix('admin/akun')->name('admin.akun.')->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('index');
    Route::get('/create', [UserManagementController::class, 'create'])->name('create');
    Route::post('/', [UserManagementController::class, 'store'])->name('store');
});

// ──────────────────────────────────────────
// Pasien Routes (Dashboard, Profil, Antrean, Konsultasi)
// ──────────────────────────────────────────
Route::middleware(['auth', 'role:pasien'])->group(function () {
    // Dashboard Pasien
    Route::get('/pasien/dashboard', [PatientDashboardController::class, 'index'])->name('pasien.dashboard');

    // PBI-04: Profil Identitas Pasien
    Route::prefix('pasien/profil')->name('pasien.profil.')->group(function () {
        Route::get('/', [PatientProfileController::class, 'edit'])->name('edit');
        Route::put('/', [PatientProfileController::class, 'update'])->name('update');
    });

    // PBI-09: Antrean Online
    Route::prefix('antrean')->name('antrean.')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::get('/ambil', [QueueController::class, 'create'])->name('create');
        Route::post('/', [QueueController::class, 'store'])->name('store');
        Route::get('/{queue}', [QueueController::class, 'show'])->name('show');
    });

    // PBI-10: Konsultasi Online (Pasien)
    Route::prefix('konsultasi')->name('konsultasi.')->group(function () {
        Route::get('/buat', [KonsultasiOnlineController::class, 'create'])->name('create');
        Route::post('/', [KonsultasiOnlineController::class, 'store'])->name('store');
        Route::get('/{consultation}', [KonsultasiOnlineController::class, 'show'])->name('show');
    });
});

// ──────────────────────────────────────────
// Ruang Konsultasi (PBI-10 & PBI-11): dipakai pasien dan dokter
// Hak akses per konsultasi dicek di RuangKonsultasiController
// ──────────────────────────────────────────
Route::middleware('auth')->prefix('konsultasi')->name('konsultasi.')->group(function () {
    Route::get('/{consultation}/ruang', [RuangKonsultasiController::class, 'show'])->name('ruang');
    Route::post('/{consultation}/pesan', [RuangKonsultasiController::class, 'kirim'])->name('pesan');
});

// ──────────────────────────────────────────
// Dokter Routes (PBI-11 Konsultasi Dokter)
// ──────────────────────────────────────────
Route::middleware(['auth', 'role:dokter'])->prefix('dokter/konsultasi')->name('dokter.konsultasi.')->group(function () {
    Route::get('/', [DokterKonsultasiController::class, 'index'])->name('index');
    Route::post('/{consultation}/mulai', [DokterKonsultasiController::class, 'mulai'])->name('mulai');
    Route::post('/{consultation}/selesai', [DokterKonsultasiController::class, 'selesai'])->name('selesai');
});