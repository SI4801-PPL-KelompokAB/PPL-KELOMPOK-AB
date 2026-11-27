# 06. Testing

Setiap PBI wajib punya test otomatis yang lulus sebelum pindah ke In Review. Ada dua jenis:

| Jenis | Alat | Untuk | Lokasi |
|---|---|---|---|
| Feature test | PHPUnit (bawaan Laravel) | Aturan bisnis di service dan respons HTTP, cepat dijalankan | `tests/Feature/` |
| Browser test | Laravel Dusk | Alur pengguna di browser, sesuai syarat kelas | `tests/Browser/` |

Minimal per PBI: **satu test Dusk skenario normal dan satu test Dusk skenario exception**. Aturan bisnis yang punya banyak cabang (misalnya validasi PBI09 atau transisi status PBI16) sebaiknya juga punya feature test untuk tiap cabangnya.

## Setup Dusk (sekali, oleh Galih)

```bash
composer require laravel/dusk --dev
php artisan dusk:install
```

Buat database MySQL terpisah `bonamu_testing`, lalu file `.env.dusk.local` (tidak di-commit):

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_DATABASE=bonamu_testing
DB_USERNAME=root
DB_PASSWORD=
```

Untuk feature test, aktifkan di `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="bonamu_testing"/>
```

Jangan pernah menjalankan test terhadap database `bonamu` milik development, karena test menghapus isinya.

## Menjalankan

```bash
php artisan test                 # feature test
php artisan serve &              # Dusk butuh aplikasi berjalan
php artisan dusk                 # semua browser test
php artisan dusk --filter=PBI09  # hanya test PBI09
```

Dusk memakai Chrome. Bila error versi ChromeDriver, jalankan `php artisan dusk:chrome-driver --detect`.

## Penamaan

- File: `tests/Browser/PBI09BuatAntreanOnlineTest.php`, `tests/Feature/PBI09AntreanServiceTest.php`
- Method: `test_normal_<apa>` dan `test_exception_<apa>`

```php
public function test_normal_pasien_mendapat_nomor_antrean(): void
public function test_exception_kuota_penuh_ditolak(): void
public function test_exception_pasien_sudah_punya_antrean_aktif(): void
```

Awalan `PBI09` membuat `--filter=PBI09` dan pelacakan ke Jira jadi mudah.

## Aturan menulis test

- Pakai trait `DatabaseMigrations` (Dusk) atau `RefreshDatabase` (feature test), lalu jalankan `ReferensiSeeder` (memanggil `RoleSeeder`, `JenisPasienSeeder`, `StatusAntreanSeeder`, `CabangSeeder`) di awal test.
- Siapkan data lewat factory, jangan bergantung pada isi `DemoSeeder`.
- Cari elemen di Dusk lewat atribut `dusk`, misalnya `->click('@tombol-ambil-antrean')`, bukan lewat class CSS atau teks tombol yang bisa berubah.
- Untuk tanggal, gunakan `Carbon::setTestNow()` di feature test supaya hasilnya tidak bergantung hari test dijalankan.
- Satu test memeriksa satu hal. Nama test harus menjelaskan apa yang diharapkan.

## Contoh Dusk

```php
class PBI09BuatAntreanOnlineTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_exception_kuota_penuh_ditolak(): void
    {
        $this->seed(ReferensiSeeder::class);
        $jadwal = JadwalPraktik::factory()->kuota(1)->create();
        Antrean::factory()->untukJadwal($jadwal)->create();
        $pasien = User::factory()->pasien()->denganProfil()->create();

        $this->browse(function (Browser $browser) use ($pasien, $jadwal) {
            $browser->loginAs($pasien)
                ->visitRoute('pasien.antrean.create')
                ->select('@pilih-dokter', $jadwal->dokter_id)
                ->type('@tanggal', $jadwal->tanggalBerikutnya()->format('Y-m-d'))
                ->click('@tombol-ambil-antrean')
                ->assertSee('Jadwal Penuh');
        });
    }
}
```

Nama factory state (`kuota`, `untukJadwal`, `pasien`, `denganProfil`) dan method `tanggalBerikutnya()` di atas adalah contoh. Nama sebenarnya mengikuti factory dan model yang dibuat Galih.

## Tabel skenario di Jira

Setiap issue PBI melampirkan tabel ini (syarat asprak):

| No | Skenario | Jenis | Input | Output yang diharapkan | Nama test |
|---|---|---|---|---|---|
| 1 | Pasien mengambil antrean saat kuota tersedia | Normal | Poli Umum, dr. Contoh, besok | Nomor 1, status Menunggu | `test_normal_pasien_mendapat_nomor_antrean` |
| 2 | Kuota dokter sudah penuh | Exception | Jadwal kuota 1, sudah ada 1 antrean | Pesan "Jadwal Penuh" | `test_exception_kuota_penuh_ditolak` |
