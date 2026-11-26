<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminQueueController;
use App\Http\Controllers\Admin\CabangController;
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

// halaman utama: kalau sudah login masuk ke dashboard, kalau belum masuk ke halaman login
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// route registrasi pasien baru, login, dan logout
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// pengarah dashboard otomatis sesuai role user yang sedang login
Route::get('/dashboard', function () {
    $role = Auth::user()?->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'pasien' => redirect()->route('pasien.dashboard'),
        'dokter' => redirect()->route('dokter.konsultasi.index'),
        default => redirect('/login'),
    };
})->middleware('auth')->name('dashboard');

// tombol ganti role cepat khusus untuk mempermudah pengujian
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

// grup route khusus admin (hanya bisa diakses oleh role admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // halaman dashboard admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // kelola antrean pasien oleh admin
    Route::get('/antrean', [AdminQueueController::class, 'index'])->name('antrean.index');
    Route::patch('/antrean/{queue}/status', [AdminQueueController::class, 'updateStatus'])->name('antrean.update-status');

    // halaman edit profil admin
    Route::get('/profil', [AdminProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [AdminProfileController::class, 'update'])->name('profil.update');

    // pbi 05: kelola data poli klinik
    Route::resource('poli', PoliController::class)->except(['show', 'destroy']);
    Route::patch('poli/{poli}/toggle-status', [PoliController::class, 'toggleStatus'])->name('poli.toggle-status');

    // pbi 12: kelola data cabang klinik
    Route::resource('cabang', CabangController::class)->except(['show', 'destroy']);
    Route::patch('cabang/{cabang}/toggle-status', [CabangController::class, 'toggleStatus'])->name('cabang.toggle-status');

    // =====================================================================
    // [pbi 07 - spk-36] milik nara: kelola jadwal praktik dokter (sprint 1)
    // =====================================================================
    Route::resource('jadwal', DoctorScheduleController::class)
        ->parameters(['jadwal' => 'doctorSchedule'])
        ->except(['show']);
    Route::post('jadwal/{doctorSchedule}/toggle-status', [DoctorScheduleController::class, 'toggleStatus'])
        ->name('jadwal.toggle-status');
});

// pbi 03: admin membuat akun dokter dan staf admin
Route::prefix('admin/akun')->name('admin.akun.')->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('index');
    Route::get('/create', [UserManagementController::class, 'create'])->name('create');
    Route::post('/', [UserManagementController::class, 'store'])->name('store');
});

// grup route khusus pasien (hanya bisa diakses oleh role pasien)
Route::middleware(['auth', 'role:pasien'])->group(function () {
    // halaman dashboard pasien
    Route::get('/pasien/dashboard', [PatientDashboardController::class, 'index'])->name('pasien.dashboard');

    // pbi 04: kelola profil identitas pasien
    Route::prefix('pasien/profil')->name('pasien.profil.')->group(function () {
        Route::get('/', [PatientProfileController::class, 'edit'])->name('edit');
        Route::put('/', [PatientProfileController::class, 'update'])->name('update');
    });

    // =====================================================================
    // milik nara: antrean online pasien
    // - [pbi 09 - spk-4] pendaftaran & lihat tiket antrean (sprint 1)
    // - [pbi 16 - spk-44] pembatalan & reschedule antrean (sprint 2)
    // =====================================================================
    Route::prefix('antrean')->name('antrean.')->group(function () {
        // [pbi 09 - spk-4]
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::get('/ambil', [QueueController::class, 'create'])->name('create');
        Route::post('/', [QueueController::class, 'store'])->name('store');
        Route::get('/{queue}', [QueueController::class, 'show'])->name('show');

        // [pbi 16 - spk-44]
        Route::get('/{queue}/reschedule', [QueueController::class, 'rescheduleForm'])->name('reschedule.form');
        Route::put('/{queue}/reschedule', [QueueController::class, 'reschedule'])->name('reschedule');
        Route::post('/{queue}/batal', [QueueController::class, 'cancel'])->name('cancel');
    });

    // pbi 10: konsultasi online dari sisi pasien
    Route::prefix('konsultasi')->name('konsultasi.')->group(function () {
        Route::get('/buat', [KonsultasiOnlineController::class, 'create'])->name('create');
        Route::post('/', [KonsultasiOnlineController::class, 'store'])->name('store');
        Route::get('/{consultation}', [KonsultasiOnlineController::class, 'show'])->name('show');
    });
});

// ruang konsultasi (pbi 10 & pbi 11): dipakai bersama oleh pasien dan dokter
Route::middleware('auth')->prefix('konsultasi')->name('konsultasi.')->group(function () {
    Route::get('/{consultation}/ruang', [RuangKonsultasiController::class, 'show'])->name('ruang');
    Route::post('/{consultation}/pesan', [RuangKonsultasiController::class, 'kirim'])->name('pesan');
});

// pbi 11: penanganan konsultasi online dari sisi dokter
Route::middleware(['auth', 'role:dokter'])->prefix('dokter/konsultasi')->name('dokter.konsultasi.')->group(function () {
    Route::get('/', [DokterKonsultasiController::class, 'index'])->name('index');
    Route::post('/{consultation}/mulai', [DokterKonsultasiController::class, 'mulai'])->name('mulai');
    Route::post('/{consultation}/selesai', [DokterKonsultasiController::class, 'selesai'])->name('selesai');
});
