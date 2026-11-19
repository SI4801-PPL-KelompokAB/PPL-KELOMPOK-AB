<?php

namespace Tests\Browser;

use App\Models\KlinikCabang;
use App\Models\Layanan;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI12TarifLayananDuskTest extends DuskTestCase
{
    private User $admin;
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
     * Skenario Normal PBI-12 (Kelola Layanan dan Tarif):
     * 1. Login sebagai Admin
     * 2. Buka menu Layanan & Tarif (/admin/layanan)
     * 3. Tambah layanan medis baru (Pemeriksaan Darah Rutin Dusk, Tarif Umum: 125000, BPJS: 0)
     * 4. Ubah tarif umum menjadi 150000
     * 5. Nonaktifkan status layanan
     * 6. Pembuktian Database MySQL: Seluruh perubahan tercatat nyata di tabel 'layanans'
     */
    public function test_alur_lengkap_pbi12_kelola_layanan_dan_tarif_melalui_browser_dan_database(): void
    {
        $namaUji = 'Pemeriksaan Darah Rutin Dusk';
        Layanan::where('nama_layanan', $namaUji)->delete();

        $this->browse(function (Browser $browser) use ($namaUji) {
            // 1. Pastikan login Admin & buka menu Layanan & Tarif (PBI-12)
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif')
                ->assertSee('Tambah Layanan');

            // 3. Buka form tambah layanan jika terlipat
            $browser->script("
                const formCollapse = document.getElementById('collapseFormLayanan');
                if (formCollapse && !formCollapse.classList.contains('show')) {
                    formCollapse.classList.add('show');
                }
            ");

            // 4. Isi data layanan & tarif baru
            $browser->value('#nama_layanan', $namaUji)
                ->value('#kode_layanan', 'LAB-DUSK')
                ->value('#tarif_umum', '125000')
                ->value('#tarif_bpjs', '0')
                ->value('#deskripsi', 'Pemeriksaan darah lengkap laboratorium');

            // Submit form tambah
            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-layanan"]'))->submit();

            // 5. Verifikasi di browser
            $browser->waitForText('Layanan dan tarif baru berhasil ditambahkan.')
                ->assertSee($namaUji)
                ->assertSee('Rp 125.000')
                ->assertSee('Rp 0');

            // Pembuktian Database Tahap 1: Record tersimpan nyata
            $layanan = Layanan::where('nama_layanan', $namaUji)->first();
            $this->assertNotNull($layanan, 'Data layanan harus tersimpan di database MySQL.');
            $this->assertEquals(125000, (int) $layanan->tarif_umum);
            $this->assertEquals(0, (int) $layanan->tarif_bpjs);
            $this->assertEquals(1, $layanan->status);

            // 6. Ubah tarif layanan
            $browser->visit(route('admin.layanan.index', ['edit' => $layanan->id]))
                ->waitForText('Ubah Layanan & Tarif');

            $browser->value('#tarif_umum', '150000');
            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-edit-layanan"]'))->submit();

            // Verifikasi update di browser
            $browser->waitForText('Data layanan dan tarif berhasil diperbarui.')
                ->assertSee('Rp 150.000');

            // Pembuktian Database Tahap 2: Tarif terupdate
            $layanan->refresh();
            $this->assertEquals(150000, (int) $layanan->tarif_umum);

            // 7. Nonaktifkan status layanan
            $browser->driver->findElement(WebDriverBy::cssSelector("form[action*='{$layanan->id}/toggle-status']"))->submit();

            // Verifikasi status nonaktif di browser
            $browser->waitForText("berhasil dinonaktifkan.")
                ->assertSee('Nonaktif');

            // Pembuktian Database Tahap 3: Status 0 (Nonaktif)
            $layanan->refresh();
            $this->assertEquals(0, $layanan->status);
        });

        // Verifikasi Akhir di Database
        $this->assertDatabaseHas('layanans', [
            'nama_layanan' => $namaUji,
            'tarif_umum' => 150000,
            'status' => 0,
        ]);
    }

    /**
     * Skenario Exception PBI-12:
     * Tambah layanan ditolak jika nama layanan sudah digunakan di cabang yang sama.
     */
    public function test_exception_tambah_layanan_ditolak_jika_nama_duplikat(): void
    {
        // Buat 1 layanan eksisting
        $existing = Layanan::firstOrCreate(
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
                ->script("
                    const formCollapse = document.getElementById('collapseFormLayanan');
                    if (formCollapse && !formCollapse.classList.contains('show')) {
                        formCollapse.classList.add('show');
                    }
                ");

            $browser->value('#nama_layanan', $existing->nama_layanan)
                ->value('#tarif_umum', '75000')
                ->value('#tarif_bpjs', '0');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-layanan"]'))->submit();

            $browser->waitForText('Nama layanan sudah digunakan di cabang ini.')
                ->assertSee('Nama layanan sudah digunakan di cabang ini.');
        });
    }

    /**
     * Skenario Exception PBI-12:
     * Tambah layanan ditolak jika tarif negatif.
     */
    public function test_exception_tambah_layanan_ditolak_jika_tarif_negatif(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/layanan');

            $browser->waitForText('Layanan dan Tarif')
                ->script("
                    const formCollapse = document.getElementById('collapseFormLayanan');
                    if (formCollapse && !formCollapse.classList.contains('show')) {
                        formCollapse.classList.add('show');
                    }
                ");

            $browser->value('#nama_layanan', 'Layanan Tarif Minus')
                ->value('#tarif_umum', '-10000') // Tarif tidak boleh minus
                ->value('#tarif_bpjs', '0');

            $browser->driver->findElement(WebDriverBy::cssSelector('form[dusk="form-tambah-layanan"]'))->submit();

            $browser->waitForText('Tarif umum tidak boleh negatif.')
                ->assertSee('Tarif umum tidak boleh negatif.');
        });
    }
}
