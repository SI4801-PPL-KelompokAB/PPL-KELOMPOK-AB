# 04. Konvensi Kode

## Setup lokal

1. Clone repo, lalu pindah ke branch-mu (lihat `05-alur-git-dan-jira.md`).
2. `composer install` dan `npm install`
3. Salin `.env.example` menjadi `.env`, lalu `php artisan key:generate`
4. Buat database MySQL kosong bernama `bonamu` (lewat phpMyAdmin XAMPP atau MySQL CLI)
5. `php artisan migrate:fresh --seed`
6. Jalankan `php artisan serve` dan `npm run dev` di dua terminal

Isi `.env` yang wajib sama di semua anggota:

```env
APP_NAME=Bonamu
APP_LOCALE=id
APP_FAKER_LOCALE=id_ID
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bonamu
DB_USERNAME=root
DB_PASSWORD=
```

Zona waktu aplikasi diatur di `config/app.php` menjadi `'timezone' => 'Asia/Jakarta'`, karena validasi tanggal antrean bergantung pada tanggal lokal.

## Setup awal yang dikerjakan Galih (sekali saja)

Repo saat ini masih memakai bawaan skeleton (Tailwind dan SQLite). Sebelum anggota mulai coding:

- [ ] Ubah `.env.example` sesuai blok di atas
- [ ] Ganti Tailwind ke Bootstrap 5: `npm remove tailwindcss @tailwindcss/vite`, lalu `npm install bootstrap @popperjs/core`
- [ ] `vite.config.js`: hapus plugin `tailwindcss()`
- [ ] `resources/css/app.css`: hapus isi Tailwind, ganti dengan `@import 'bootstrap/dist/css/bootstrap.min.css';`
- [ ] `resources/js/app.js`: tambahkan `import * as bootstrap from 'bootstrap'; window.bootstrap = bootstrap;`
- [ ] `config/app.php`: timezone `Asia/Jakarta`
- [ ] Pasang Dusk (lihat `06-testing.md`)
- [ ] Migration, seeder, dan factory sesuai `03-skema-database.md`
- [ ] Layout dasar `resources/views/layouts/app.blade.php` dengan navbar per role
- [ ] Middleware role, trait `BelongsToCabang`, dan `AturanBisnisException` seperti di bawah
- [ ] Hapus file `.txt` di root repo yang tersisa dari latihan Git (`SPK-4.txt`, `create antrean online.txt`, dan lainnya)

## Struktur folder

```
app/
  Exceptions/AturanBisnisException.php
  Http/
    Controllers/
      Auth/                 # PBI01, PBI02
      Pasien/               # Controller untuk role pasien
      Dokter/               # Controller untuk role dokter
      Admin/                # Controller untuk role admin
    Middleware/EnsureRole.php
    Requests/
      Pasien/  Dokter/  Admin/   # Form Request, satu per aksi
  Models/
    Concerns/BelongsToCabang.php
  Services/                 # Semua aturan bisnis
resources/views/
  layouts/app.blade.php
  components/              # Komponen Blade bersama (alert, badge status)
  auth/  pasien/  dokter/  admin/
routes/
  web.php                  # Auth dan include file per role
  pasien.php  dokter.php  admin.php
tests/
  Feature/                 # Test service dan HTTP
  Browser/                 # Test Dusk
```

## Penamaan

| Hal | Aturan | Contoh |
|---|---|---|
| Model | PascalCase, tunggal, Bahasa Indonesia | `Antrean`, `JadwalPraktik`, `StatusAntrean` |
| Tabel | Pluralisasi bawaan Laravel | `antreans`, `jadwal_praktiks` |
| Controller | `<Domain>Controller` di folder role | `Pasien\AntreanController`, `Admin\JadwalPraktikController` |
| Method controller | Resource method Laravel | `index`, `create`, `store`, `edit`, `update` |
| Aksi non-CRUD | Controller invokable atau method deskriptif | `Admin\PanggilAntreanController`, `mulaiLayani` |
| Service | `<Domain>Service` | `AntreanService`, `JadwalPraktikService` |
| Form Request | `<Aksi><Domain>Request` | `StoreAntreanRequest` |
| Route name | `<role>.<domain>.<aksi>` | `pasien.antrean.store`, `admin.antrean.panggil` |
| URL | Kebab-case, Bahasa Indonesia | `/admin/jadwal-praktik`, `/pasien/antrean` |
| View | `<role>/<domain>/<aksi>.blade.php` | `pasien/antrean/create.blade.php` |

## Pola request

```
Route → Middleware (auth, role) → Controller → Form Request (validasi input)
      → Service (aturan bisnis, transaksi) → Model → View / redirect
```

- **Controller** tipis: ambil input yang sudah divalidasi, panggil service, kembalikan view atau redirect. Tidak ada aturan bisnis.
- **Form Request** memvalidasi bentuk input (wajib diisi, format, panjang). Pesan error ditulis dalam Bahasa Indonesia di method `messages()`.
- **Service** memvalidasi aturan bisnis (kuota, jadwal, antrean aktif, transisi status) dan melempar `AturanBisnisException` bila dilanggar.
- CRUD master data yang sederhana (poli, layanan) boleh langsung memakai model tanpa service.

```php
// app/Exceptions/AturanBisnisException.php
namespace App\Exceptions;

use RuntimeException;

class AturanBisnisException extends RuntimeException {}
```

```php
// Contoh di controller
public function store(StoreAntreanRequest $request, AntreanService $service)
{
    try {
        $antrean = $service->buatOnline($request->user(), $request->validated());
    } catch (AturanBisnisException $e) {
        return back()->withInput()->withErrors(['antrean' => $e->getMessage()]);
    }

    return redirect()->route('pasien.antrean.show', $antrean)
        ->with('sukses', "Nomor antrean Anda: {$antrean->nomor}");
}
```

## Role dan middleware

```php
// app/Http/Middleware/EnsureRole.php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    $user = $request->user();

    if (! $user || ! $user->is_active || ! in_array($user->role->kode, $roles, true)) {
        abort(403);
    }

    return $next($request);
}
```

Didaftarkan di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
})
```

Di model `User`, sediakan `isPasien()`, `isDokter()`, `isAdmin()`, eager load role, dan nilai default `is_active`:

```php
protected $with = ['role'];

// Wajib. Tanpa ini, user buatan factory di test punya is_active = null
// sehingga middleware menolaknya dengan 403.
protected $attributes = ['is_active' => true];
```

Routes:

```php
// routes/web.php
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->name('pasien.')
    ->group(base_path('routes/pasien.php'));
Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->name('dokter.')
    ->group(base_path('routes/dokter.php'));
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')
    ->group(base_path('routes/admin.php'));
```

Route dipisah per role supaya tujuh orang tidak bentrok di satu file.

## Scope cabang

Semua model yang punya kolom `cabang_id` (`Poli`, `Dokter`, `JadwalPraktik`, `Antrean`, `Layanan`, `Konsultasi`, `Pembayaran`) memakai trait ini. **Jangan** menulis `where('cabang_id', ...)` manual untuk membatasi admin.

```php
// app/Models/Concerns/BelongsToCabang.php
namespace App\Models\Concerns;

use App\Models\Cabang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCabang
{
    protected static function bootBelongsToCabang(): void
    {
        // Admin hanya melihat data cabangnya sendiri
        static::addGlobalScope('cabang', function (Builder $query) {
            $user = auth()->user();

            if ($user && $user->isAdmin()) {
                $query->where($query->getModel()->getTable() . '.cabang_id', $user->cabang_id);
            }
        });

        // Data baru buatan admin otomatis masuk ke cabangnya
        static::creating(function (Model $model) {
            $user = auth()->user();

            if ($user && $user->isAdmin() && empty($model->cabang_id)) {
                $model->cabang_id = $user->cabang_id;
            }
        });
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }
}
```

Untuk pasien dan dokter scope ini tidak aktif. Karena itu:

- Query pasien wajib difilter pemiliknya sendiri, misalnya `$request->user()->antreans()`, bukan `Antrean::find($id)`.
- Query dokter wajib difilter dokternya sendiri, misalnya `$dokter->konsultasis()`.
- Akses ke data milik orang lain dicek dengan Policy dan dijawab 403.

## Status antrean

```php
// app/Models/StatusAntrean.php
class StatusAntrean extends Model
{
    public const MENUNGGU = 'MENUNGGU';
    public const DIPANGGIL = 'DIPANGGIL';
    public const DILAYANI = 'DILAYANI';
    public const SELESAI = 'SELESAI';
    public const DILEWATI = 'DILEWATI';

    public const AKTIF = [self::MENUNGGU, self::DIPANGGIL, self::DILAYANI]; // T02

    public static function idDari(string $kode): int
    {
        return cache()->rememberForever("status_antrean.$kode",
            fn () => static::where('kode', $kode)->value('id'));
    }
}
```

Semua perubahan status hanya lewat method di `AntreanService` (`panggil`, `mulaiLayani`, `selesai`, `lewati`, `panggilUlang`). Method ini memeriksa transisi sesuai tabel di PBI16 sebelum menyimpan.

## Transaksi dan nomor antrean

```php
return DB::transaction(function () use ($jadwal, $tanggal) {
    $nomorTerakhir = Antrean::where('dokter_id', $jadwal->dokter_id)
        ->whereDate('tanggal', $tanggal)
        ->lockForUpdate()
        ->max('nomor');

    // cek kuota di sini, di dalam transaksi yang sama
    // lalu buat antrean dengan nomor $nomorTerakhir + 1
});
```

## Tampilan

- Bootstrap 5 saja. Tidak ada Tailwind, jQuery, atau framework CSS lain.
- Semua halaman `@extends('layouts.app')`.
- Pesan sukses lewat `session('sukses')`, error lewat `$errors`, ditampilkan oleh komponen `<x-alert />`.
- Badge status antrean memakai komponen `<x-badge-status :status="$antrean->status" />` supaya warnanya sama di semua halaman.
- Elemen yang dipakai test diberi atribut `dusk="..."`, misalnya `<button dusk="tombol-ambil-antrean">`.
- Polling 10 detik memakai `fetch()` ke route JSON, bukan `<meta http-equiv="refresh">`.

## Sebelum commit

- `./vendor/bin/pint` untuk merapikan format
- `php artisan test` dan `php artisan dusk` lulus
- Tidak ada `dd()`, `dump()`, atau `console.log` yang tertinggal
- Tidak ada file `.env`, `vendor/`, atau `node_modules/` yang ikut ter-commit
