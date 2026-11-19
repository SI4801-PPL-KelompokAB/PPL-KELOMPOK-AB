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

        // Pastikan akun admin tersedia
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

        // Pastikan nomor hp dan password cocok untuk login
        $this->admin->no_hp = '081234567890';
        $this->admin->password = bcrypt('password');
        $this->admin->save();
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
     * Skenario Otomatisasi Lengkap PBI-05 (Mengelola Data Poli):
     * 1. Otomatisasi Login Admin
     * 2. Navigasi ke menu Data Poli
     * 3. Input & Simpan Poli baru (Poli Mata / PMT)
     * 4. Ubah nama Poli menjadi 'Poli Mata & Spesialis Retina'
     * 5. Nonaktifkan status Poli via konfirmasi browser dialog
     * 6. Pembuktian seluruh aksi tersimpan nyata di database MySQL (tidak di-rollback)
     */
    public function test_alur_lengkap_pbi05_kelola_data_poli_melalui_browser_dan_database(): void
    {
        // Bersihkan data uji PMT jika sisa pengujian sebelumnya
        Poli::withoutGlobalScopes()->where('kode_poli', 'PMT')->delete();

        $this->browse(function (Browser $browser) {
            // 1. Pastikan login sebagai Admin & akses halaman Data Poli
            $this->pastikanLoginAdmin($browser, '/admin/poli');

            $browser->waitForText('Data Poli')
                ->assertPresent('@tabel-poli');

            // 3. Mengisi form Tambah Poli baru menggunakan native value() Dusk
            $browser->value('#kode_poli', 'PMT')
                ->value('#nama_poli', 'Poli Mata')
                ->value('#deskripsi', 'Layanan pemeriksaan kesehatan mata, refraksi, dan penanganan katarak');

            // Submit form tambah
            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-poli"]'))->submit();

            // 4. Verifikasi notifikasi sukses dan data muncul di tabel
            $browser->waitForText('Poli berhasil ditambahkan.')
                ->assertSee('Poli Mata')
                ->assertSee('PMT');

            // Pembuktian Database Tahap 1: Data tersimpan di tabel MySQL
            $poli = Poli::withoutGlobalScopes()->where('kode_poli', 'PMT')->first();
            $this->assertNotNull($poli, 'Poli PMT harus sudah tersimpan di database MySQL.');
            $this->assertEquals('Poli Mata', $poli->nama_poli);
            $this->assertEquals(1, $poli->status);

            // 5. Ubah data Poli (Klik tombol Ubah pada baris poli PMT)
            $browser->script("document.querySelector('[dusk=\"tombol-edit-{$poli->id}\"]').click();");
            $browser->waitForText('Ubah Poli');

            // Ubah nama poli
            $browser->value('#nama_poli', 'Poli Mata & Spesialis Retina');
            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-edit-poli"]'))->submit();

            // 6. Verifikasi perubahan sukses di browser
            $browser->waitForText('Poli berhasil diperbarui.')
                ->assertSee('Poli Mata & Spesialis Retina');

            // Pembuktian Database Tahap 2: Nama terupdate di database MySQL
            $poli->refresh();
            $this->assertEquals('Poli Mata & Spesialis Retina', $poli->nama_poli);

            // 7. Nonaktifkan status Poli (Toggle status)
            $browser->driver->findElement(WebDriverBy::cssSelector("form[dusk='form-toggle-{$poli->id}']"))->submit();

            // 8. Verifikasi status berubah menjadi Nonaktif di browser
            $browser->waitForText('Poli berhasil dinonaktifkan.')
                ->waitFor("@badge-nonaktif-{$poli->id}")
                ->assertPresent("@badge-nonaktif-{$poli->id}");

            // Pembuktian Database Tahap 3: Status di database MySQL berubah menjadi 0 (Nonaktif)
            $poli->refresh();
            $this->assertEquals(0, $poli->status);
        });

        // Verifikasi Akhir: Data PMT tetap tersimpan nyata di database MySQL untuk diperiksa oleh dosen
        $this->assertDatabaseHas('polis', [
            'kode_poli' => 'PMT',
            'nama_poli' => 'Poli Mata & Spesialis Retina',
            'status' => 0,
        ]);
    }

    /**
     * Skenario Exception PBI-05:
     * Tambah poli baru ditolak jika kode poli sudah digunakan di cabang yang sama.
     */
    public function test_exception_tambah_poli_ditolak_jika_kode_poli_sudah_digunakan(): void
    {
        // Pastikan kode poli PU sudah ada di database
        Poli::withoutGlobalScopes()->firstOrCreate(
            ['kode_poli' => 'PU'],
            [
                'klinik_cabang_id' => $this->cabang->id,
                'nama_poli' => 'Poli Umum Eksisting',
                'deskripsi' => 'Layanan medis umum',
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/poli');

            $browser->waitForText('Data Poli')
                ->value('#kode_poli', 'PU') // Kode PU sudah ada di seeder (Poli Umum)
                ->value('#nama_poli', 'Poli Duplikat')
                ->value('#deskripsi', 'Poli dengan kode yang bentrok');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-poli"]'))->submit();

            $browser->waitForText('Kode poli sudah digunakan di cabang ini.')
                ->assertSee('Kode poli sudah digunakan di cabang ini.');
        });
    }
}
