<?php

namespace Tests\Browser;

use App\Models\KlinikCabang;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI05PoliTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $admin;

    private KlinikCabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang = KlinikCabang::create([
            'nama_cabang' => 'Klinik Bona Mitra Keluarga Pusat',
            'alamat' => 'Jl. Contoh No. 1',
            'nomor_telepon' => '0221234567',
            'status' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Skenario Normal: Admin menambah poli baru melalui form.
     */
    public function test_normal_admin_menambah_poli_baru(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.poli.index')
                ->click('@tombol-tambah-poli')
                ->type('@input-kode-poli', 'INT')
                ->type('@input-nama-poli', 'Poli Penyakit Dalam')
                ->type('@input-deskripsi', 'Pemeriksaan organ dalam')
                ->click('@tombol-simpan')
                ->assertRouteIs('admin.poli.index')
                ->assertSee('Poli Penyakit Dalam')
                ->assertSee('Poli berhasil ditambahkan.');
        });
    }

    /**
     * Skenario Exception: Nama poli duplikat menampilkan pesan error validasi.
     */
    public function test_exception_nama_poli_duplikat_ditolak(): void
    {
        Poli::create([
            'klinik_cabang_id' => $this->cabang->id,
            'kode_poli' => 'GIG',
            'nama_poli' => 'Poli Gigi',
            'deskripsi' => 'Layanan gigi',
            'status' => true,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.poli.index')
                ->type('@input-kode-poli', 'GIG2')
                ->type('@input-nama-poli', 'Poli Gigi')
                ->click('@tombol-simpan')
                ->assertSee('Nama poli sudah digunakan di cabang ini.');
        });
    }
}
