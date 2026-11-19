<?php

namespace Tests\Browser;

use App\Models\KlinikCabang;
use App\Models\Poli;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI05PoliDuskTest extends DuskTestCase
{
    private User $admin;
    private User $pasien;
    private KlinikCabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan cabang klinik tersedia di database MySQL
        $this->cabang = KlinikCabang::firstOrCreate(
            ['nama_cabang' => 'Klinik Bonamu Pusat'],
            [
                'alamat' => 'Jl. Sukabirus No. 12, Bandung',
                'nomor_telepon' => '0221234567',
                'status' => true,
            ]
        );

        // Akun Admin
        $this->admin = User::firstOrCreate(
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
        $this->admin->no_hp = '081234567890';
        $this->admin->password = bcrypt('password');
        $this->admin->save();

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
     * TC-01: Admin menambah data poli baru dengan data lengkap dan valid
     */
    public function test_tc01_admin_menambah_poli_baru_dengan_data_valid(): void
    {
        Poli::withoutGlobalScopes()->where('kode_poli', 'PED')->delete();

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');

            $browser->waitForText('Data Poli')
                ->assertPresent('@tabel-poli')
                ->value('#kode_poli', 'PED')
                ->value('#nama_poli', 'Poli Anak (Pediatri)')
                ->value('#deskripsi', 'Layanan kesehatan dan imunisasi anak');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-poli"]'))->submit();

            $browser->waitForText('Poli berhasil ditambahkan.')
                ->assertSee('Poli Anak (Pediatri)')
                ->assertSee('PED');

            $this->assertDatabaseHas('polis', [
                'kode_poli' => 'PED',
                'nama_poli' => 'Poli Anak (Pediatri)',
                'status' => 1,
            ]);
        });
    }

    /**
     * TC-02: Admin mengubah nama dan deskripsi poli yang sudah ada
     */
    public function test_tc02_admin_mengubah_nama_dan_deskripsi_poli(): void
    {
        $poli = Poli::withoutGlobalScopes()->firstOrCreate(
            ['kode_poli' => 'PGT'],
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_poli' => 'Poli Gigi dan Mulut',
                'deskripsi' => 'Layanan gigi umum',
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) use ($poli) {
            $this->pastikanLoginAdmin($browser, route('admin.poli.edit', $poli));

            $browser->waitForText('Ubah Poli')
                ->value('#nama_poli', 'Poli Gigi & Mulut Spesialis')
                ->value('#deskripsi', 'Layanan pemeriksaan gigi lengkap dan bedah mulut');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-edit-poli"]'))->submit();

            $browser->waitForText('Poli berhasil diperbarui.')
                ->assertSee('Poli Gigi & Mulut Spesialis');

            $poli->refresh();
            $this->assertEquals('Poli Gigi & Mulut Spesialis', $poli->nama_poli);
        });
    }

    /**
     * TC-03: Admin menonaktifkan status operasional poli (toggle status aktif ke nonaktif)
     */
    public function test_tc03_admin_menonaktifkan_status_operasional_poli(): void
    {
        $poli = Poli::withoutGlobalScopes()->firstOrCreate(
            ['kode_poli' => 'IMN'],
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_poli' => 'Poli Imunisasi',
                'deskripsi' => 'Layanan vaksinasi dan imunisasi',
                'status' => true,
            ]
        );
        $poli->update(['status' => true]);

        $this->browse(function (Browser $browser) use ($poli) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');
            $browser->waitForText('Data Poli');

            $browser->driver->findElement(WebDriverBy::cssSelector("form[dusk='form-toggle-{$poli->id}']"))->submit();

            $browser->waitForText('Poli berhasil dinonaktifkan.')
                ->waitFor("@badge-nonaktif-{$poli->id}")
                ->assertPresent("@badge-nonaktif-{$poli->id}");

            $poli->refresh();
            $this->assertEquals(0, $poli->status);
        });
    }

    /**
     * TC-04: Admin mengaktifkan kembali status poli yang sebelumnya nonaktif
     */
    public function test_tc04_admin_mengaktifkan_kembali_status_poli(): void
    {
        $poli = Poli::withoutGlobalScopes()->firstOrCreate(
            ['kode_poli' => 'KIA'],
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_poli' => 'Poli KIA',
                'deskripsi' => 'Kesehatan ibu dan anak',
                'status' => false,
            ]
        );
        $poli->update(['status' => false]);

        $this->browse(function (Browser $browser) use ($poli) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');
            $browser->waitForText('Data Poli');

            $browser->driver->findElement(WebDriverBy::cssSelector("form[dusk='form-toggle-{$poli->id}']"))->submit();

            $browser->waitForText('Poli berhasil diaktifkan.')
                ->waitFor("@badge-aktif-{$poli->id}")
                ->assertPresent("@badge-aktif-{$poli->id}");

            $poli->refresh();
            $this->assertEquals(1, $poli->status);
        });
    }

    /**
     * TC-05: Admin membuka dan melihat daftar seluruh data poli klinik
     */
    public function test_tc05_admin_melihat_daftar_seluruh_data_poli(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');

            $browser->waitForText('Daftar Poli')
                ->assertPresent('@tabel-poli')
                ->assertSee('KODE')
                ->assertSee('NAMA')
                ->assertSee('STATUS');
        });
    }

    /**
     * TC-06: Admin menyimpan poli dengan kolom wajib (kode atau nama) dikosongkan
     */
    public function test_tc06_admin_simpan_kolom_wajib_kosong_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');

            $browser->waitForText('Data Poli')
                ->script("
                    document.getElementById('kode_poli').removeAttribute('required');
                    document.getElementById('nama_poli').removeAttribute('required');
                ");

            $browser->value('#kode_poli', '')
                ->value('#nama_poli', '');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-poli"]'))->submit();

            $browser->waitForText('Kode poli wajib diisi.')
                ->assertSee('Kode poli wajib diisi.')
                ->assertSee('Nama poli wajib diisi.');
        });
    }

    /**
     * TC-07: Admin menambah poli dengan nama yang sudah terdaftar pada cabang klinik yang sama (duplikat)
     */
    public function test_tc07_admin_tambah_poli_nama_duplikat_ditolak(): void
    {
        $existing = Poli::withoutGlobalScopes()->firstOrCreate(
            ['kode_poli' => 'PU'],
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_poli' => 'Poli Umum',
                'deskripsi' => 'Layanan umum',
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) use ($existing) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');

            $browser->waitForText('Data Poli')
                ->value('#kode_poli', 'PU-NEW')
                ->value('#nama_poli', $existing->nama_poli);

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-poli"]'))->submit();

            $browser->waitForText('Nama poli sudah digunakan di cabang ini.')
                ->assertSee('Nama poli sudah digunakan di cabang ini.');
        });
    }

    /**
     * TC-08: Admin menambah poli dengan kode yang sudah terdaftar pada cabang klinik yang sama (duplikat)
     */
    public function test_tc08_admin_tambah_poli_kode_duplikat_ditolak(): void
    {
        $existing = Poli::withoutGlobalScopes()->firstOrCreate(
            ['kode_poli' => 'PU'],
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_poli' => 'Poli Umum',
                'deskripsi' => 'Layanan umum',
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) use ($existing) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');

            $browser->waitForText('Data Poli')
                ->value('#kode_poli', $existing->kode_poli)
                ->value('#nama_poli', 'Poli Unik Baru');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-poli"]'))->submit();

            $browser->waitForText('Kode poli sudah digunakan di cabang ini.')
                ->assertSee('Kode poli sudah digunakan di cabang ini.');
        });
    }

    /**
     * TC-09: Admin menginput panjang karakter kode poli melebihi batas maksimal (20 karakter)
     */
    public function test_tc09_admin_input_karakter_melebihi_batas_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');

            $browser->waitForText('Data Poli')
                ->script("document.getElementById('kode_poli').removeAttribute('maxlength');");

            $browser->value('#kode_poli', str_repeat('A', 25))
                ->value('#nama_poli', 'Poli Kode Terlalu Panjang');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-poli"]'))->submit();

            $browser->waitForText('Kode poli maksimal 20 karakter.')
                ->assertSee('Kode poli maksimal 20 karakter.');
        });
    }

    /**
     * TC-10: Pengguna dengan role selain admin (pasien) dilarang mengakses URL data poli admin
     */
    public function test_tc10_pengguna_non_admin_dilarang_akses_data_poli(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->pasien)
                ->visit('/admin/poli');

            // Non-admin ditolak (403 forbidden atau diarahkan ke dashboard pasien)
            $isBlocked = str_contains($browser->driver->getCurrentURL(), '/pasien') ||
                         str_contains($browser->driver->getPageSource(), '403') ||
                         str_contains($browser->driver->getPageSource(), 'Unauthorized') ||
                         !str_contains($browser->driver->getCurrentURL(), '/admin/poli');

            $this->assertTrue($isBlocked, 'Pengguna non-admin harus dilarang mengakses halaman kelola poli.');
        });
    }
}
