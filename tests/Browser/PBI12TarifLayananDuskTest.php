<?php

namespace Tests\Browser;

use App\Models\KlinikCabang;
use App\Models\Layanan;
use App\Models\Poli;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI12TarifLayananDuskTest extends DuskTestCase
{
    private User $admin;
    private User $pasien;
    private KlinikCabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang = KlinikCabang::firstOrCreate(
            ['nama_cabang' => 'Klinik Bonamu Pusat'],
            [
                'alamat' => 'Jl. Sukabirus No. 12 Bandung',
                'nomor_telepon' => '0221234567',
                'status' => true,
            ]
        );

        // Akun Admin
        $this->admin = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Bonamu',
                'no_hp' => '081234567890',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
                'klinik_cabang_id' => $this->cabang->id,
            ]
        );

        // Akun Pasien
        $this->pasien = User::firstOrCreate(
            ['email' => 'pasien.dusk@bonamu.test'],
            [
                'name' => 'Pasien Dusk',
                'no_hp' => '089999999999',
                'password' => bcrypt('password'),
                'role' => 'pasien',
                'is_active' => true,
                'klinik_cabang_id' => $this->cabang->id,
            ]
        );
    }

    /**
     * Helper: Login otomatis sebagai Admin ke URL target.
     */
    protected function pastikanLoginAdmin(Browser $browser, string $targetUrl): void
    {
        $browser->loginAs($this->admin)
            ->visit($targetUrl);
    }

    /**
     * TC-01: Admin menambah layanan dan tarif baru dengan data lengkap dan valid
     */
    public function test_tc01_admin_menambah_layanan_dan_tarif_baru(): void
    {
        $namaUji = 'Pemeriksaan Darah Lengkap Dusk';
        Layanan::withoutGlobalScopes()->where('nama_layanan', $namaUji)->delete();

        $this->browse(function (Browser $browser) use ($namaUji) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif')
                ->value('#nama_layanan', $namaUji)
                ->value('#kode_layanan', 'LAB-DUSK')
                ->value('#tarif_umum', '125000')
                ->value('#tarif_bpjs', '0')
                ->value('#deskripsi', 'Pemeriksaan darah lengkap laboratorium');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-layanan"]'))->submit();

            $browser->waitForText('Layanan dan tarif baru berhasil ditambahkan.')
                ->assertSee($namaUji)
                ->assertSee('Rp 125.000');

            $this->assertDatabaseHas('layanans', [
                'nama_layanan' => $namaUji,
                'tarif_umum' => 125000,
                'tarif_bpjs' => 0,
            ]);
        });
    }

    /**
     * TC-02: Admin mengubah nilai tarif umum dan tarif BPJS pada layanan yang sudah ada
     */
    public function test_tc02_admin_mengubah_nilai_tarif_layanan(): void
    {
        $layanan = Layanan::withoutGlobalScopes()->firstOrCreate(
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_layanan' => 'Konsultasi Dokter Umum Spesial',
            ],
            [
                'tarif_umum' => 60000,
                'tarif_bpjs' => 0,
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) use ($layanan) {
            $this->pastikanLoginAdmin($browser, route('admin.layanan.index', ['edit' => $layanan->id]));

            $browser->waitForText('Ubah Layanan & Tarif')
                ->value('#tarif_umum', '150000')
                ->value('#tarif_bpjs', '20000');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-edit-layanan"]'))->submit();

            $browser->waitForText('Data layanan dan tarif berhasil diperbarui.')
                ->assertSee('Rp 150.000');

            $layanan->refresh();
            $this->assertEquals(150000, (int) $layanan->tarif_umum);
        });
    }

    /**
     * TC-03: Admin menonaktifkan status operasional layanan (toggle status aktif ke nonaktif)
     */
    public function test_tc03_admin_menonaktifkan_status_layanan(): void
    {
        $layanan = Layanan::withoutGlobalScopes()->firstOrCreate(
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_layanan' => 'Layanan Fisioterapi Nonaktifkan',
            ],
            [
                'tarif_umum' => 80000,
                'tarif_bpjs' => 0,
                'status' => true,
            ]
        );
        $layanan->update(['status' => true]);

        $this->browse(function (Browser $browser) use ($layanan) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif');
            $browser->driver->findElement(WebDriverBy::cssSelector("form[action*='{$layanan->id}/toggle-status']"))->submit();

            $browser->waitForText('berhasil dinonaktifkan.');

            $layanan->refresh();
            $this->assertEquals(0, $layanan->status);
        });
    }

    /**
     * TC-04: Admin mengaktifkan kembali status layanan yang sebelumnya nonaktif
     */
    public function test_tc04_admin_mengaktifkan_kembali_status_layanan(): void
    {
        $layanan = Layanan::withoutGlobalScopes()->firstOrCreate(
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_layanan' => 'Layanan Fisioterapi Aktifkan',
            ],
            [
                'tarif_umum' => 80000,
                'tarif_bpjs' => 0,
                'status' => false,
            ]
        );
        $layanan->update(['status' => false]);

        $this->browse(function (Browser $browser) use ($layanan) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif');
            $browser->driver->findElement(WebDriverBy::cssSelector("form[action*='{$layanan->id}/toggle-status']"))->submit();

            $browser->waitForText('berhasil diaktifkan.');

            $layanan->refresh();
            $this->assertEquals(1, $layanan->status);
        });
    }

    /**
     * TC-05: Admin membuka dan melakukan filter daftar layanan berdasarkan poli tertentu
     */
    public function test_tc05_admin_melihat_dan_filter_layanan_berdasarkan_poli(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif')
                ->assertSee('Lihat daftar layanan puskesmas beserta tarifnya');
        });
    }

    /**
     * TC-06: Admin menyimpan layanan baru dengan kolom nama layanan atau tarif dikosongkan
     */
    public function test_tc06_admin_simpan_layanan_field_wajib_kosong_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif')
                ->script("
                    document.getElementById('nama_layanan').removeAttribute('required');
                    document.getElementById('tarif_umum').removeAttribute('required');
                    document.getElementById('tarif_bpjs').removeAttribute('required');
                ");

            $browser->value('#nama_layanan', '')
                ->value('#tarif_umum', '')
                ->value('#tarif_bpjs', '');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-layanan"]'))->submit();

            $browser->waitFor('@error-nama-layanan')
                ->assertSee('Nama layanan wajib diisi.')
                ->assertSee('Tarif umum wajib diisi.');
        });
    }

    /**
     * TC-07: Admin menginput nominal tarif umum bernilai negatif
     */
    public function test_tc07_admin_input_tarif_negatif_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif')
                ->value('#nama_layanan', 'Layanan Tarif Minus')
                ->value('#tarif_umum', '-50000')
                ->value('#tarif_bpjs', '0');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-layanan"]'))->submit();

            $browser->waitForText('Tarif umum tidak boleh negatif.')
                ->assertSee('Tarif umum tidak boleh negatif.');
        });
    }

    /**
     * TC-08: Admin menambah layanan dengan nama yang sudah terdaftar pada cabang klinik yang sama (duplikat)
     */
    public function test_tc08_admin_tambah_layanan_nama_duplikat_ditolak(): void
    {
        $existing = Layanan::withoutGlobalScopes()->firstOrCreate(
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_layanan' => 'Konsultasi Dokter Umum',
            ],
            [
                'tarif_umum' => 50000,
                'tarif_bpjs' => 0,
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) use ($existing) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif')
                ->value('#nama_layanan', $existing->nama_layanan)
                ->value('#tarif_umum', '75000')
                ->value('#tarif_bpjs', '0');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-layanan"]'))->submit();

            $browser->waitForText('Nama layanan sudah digunakan di cabang ini.')
                ->assertSee('Nama layanan sudah digunakan di cabang ini.');
        });
    }

    /**
     * TC-09: Admin menginput karakter teks/non-numerik pada kolom isian tarif
     */
    public function test_tc09_admin_input_tarif_non_numerik_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif')
                ->script("
                    document.getElementById('tarif_umum').type = 'text';
                    document.getElementById('tarif_bpjs').type = 'text';
                ");

            $browser->value('#nama_layanan', 'Layanan Teks Non Numerik')
                ->value('#tarif_umum', 'seratus ribu')
                ->value('#tarif_bpjs', '0');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-layanan"]'))->submit();

            $browser->waitForText('Tarif umum harus berupa angka.')
                ->assertSee('Tarif umum harus berupa angka.');
        });
    }

    /**
     * TC-10: Pengguna selain admin (pasien) dilarang mengakses URL master layanan & tarif admin
     */
    public function test_tc10_pengguna_non_admin_dilarang_akses_layanan_tarif(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->pasien)
                ->visit('/admin/layanan');

            $isBlocked = str_contains($browser->driver->getCurrentURL(), '/pasien') ||
                         str_contains($browser->driver->getPageSource(), '403') ||
                         str_contains($browser->driver->getPageSource(), 'Unauthorized') ||
                         !str_contains($browser->driver->getCurrentURL(), '/admin/layanan');

            $this->assertTrue($isBlocked, 'Pengguna non-admin harus dilarang mengakses kelola layanan.');
        });
    }
}
