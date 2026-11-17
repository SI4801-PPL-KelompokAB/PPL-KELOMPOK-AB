<?php

namespace Tests\Feature;

use App\Models\KlinikCabang;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PBI05PoliTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private KlinikCabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang = KlinikCabang::create([
            'nama_cabang' => 'Klinik Bona Mitra Keluarga Pusat',
            'alamat' => 'Jl. Sukabirus No. 12',
            'nomor_telepon' => '0221234567',
            'status' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Skenario Normal: Admin dapat melihat halaman daftar poli.
     */
    public function test_normal_admin_dapat_melihat_daftar_poli(): void
    {
        $poli = Poli::create([
            'klinik_cabang_id' => $this->cabang->id,
            'kode_poli' => 'PGT',
            'nama_poli' => 'Poli Gigi & Mulut',
            'deskripsi' => 'Layanan kesehatan gigi keluarga',
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.poli.index'));

        $response->assertOk();
        $response->assertViewIs('admin.poli.index');
        $response->assertSee('Poli Gigi & Mulut');
        $response->assertSee('PGT');
    }

    /**
     * Skenario Normal: Admin dapat menambahkan poli baru.
     */
    public function test_normal_admin_dapat_menambah_poli_baru(): void
    {
        $payload = [
            'kode_poli' => 'PED',
            'nama_poli' => 'Poli Anak (Pediatri)',
            'deskripsi' => 'Layanan tumbuh kembang dan imunisasi anak',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.poli.store'), $payload);

        $response->assertRedirect(route('admin.poli.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('polis', [
            'kode_poli' => 'PED',
            'nama_poli' => 'Poli Anak (Pediatri)',
            'status' => 1,
        ]);
    }

    /**
     * Skenario Normal: Admin dapat mengubah data poli.
     */
    public function test_normal_admin_dapat_mengubah_data_poli(): void
    {
        $poli = Poli::create([
            'klinik_cabang_id' => $this->cabang->id,
            'kode_poli' => 'PUL',
            'nama_poli' => 'Poli Umum Lama',
            'deskripsi' => 'Deskripsi lama',
            'status' => true,
        ]);

        $payload = [
            'kode_poli' => 'PUL',
            'nama_poli' => 'Poli Umum & Lansia',
            'deskripsi' => 'Deskripsi layanan baru',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.poli.update', $poli), $payload);

        $response->assertRedirect(route('admin.poli.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('polis', [
            'id' => $poli->id,
            'kode_poli' => 'PUL',
            'nama_poli' => 'Poli Umum & Lansia',
            'deskripsi' => 'Deskripsi layanan baru',
        ]);
    }

    /**
     * Skenario Normal: Admin dapat melakukan toggle status (aktif/nonaktif) poli.
     */
    public function test_normal_admin_dapat_toggle_status_poli(): void
    {
        $poli = Poli::create([
            'klinik_cabang_id' => $this->cabang->id,
            'kode_poli' => 'THT',
            'nama_poli' => 'Poli THT',
            'deskripsi' => 'Telinga Hidung Tenggorokan',
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.poli.toggle-status', $poli));

        $response->assertRedirect(route('admin.poli.index'));
        $this->assertDatabaseHas('polis', [
            'id' => $poli->id,
            'status' => false,
        ]);
    }

    /**
     * Skenario Exception: Nama poli duplikat di cabang yang sama ditolak.
     */
    public function test_exception_tambah_poli_dengan_nama_duplikat_di_cabang_sama_ditolak(): void
    {
        Poli::create([
            'klinik_cabang_id' => $this->cabang->id,
            'kode_poli' => 'PM1',
            'nama_poli' => 'Poli Mata',
            'deskripsi' => 'Poli pertama',
            'status' => true,
        ]);

        $payload = [
            'kode_poli' => 'PM2',
            'nama_poli' => 'Poli Mata',
            'deskripsi' => 'Mencoba tambah poli dengan nama sama',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.poli.store'), $payload);

        $response->assertSessionHasErrors(['nama_poli']);
    }

    /**
     * Skenario Exception: Kode poli duplikat di cabang yang sama ditolak.
     */
    public function test_exception_tambah_poli_dengan_kode_duplikat_di_cabang_sama_ditolak(): void
    {
        Poli::create([
            'klinik_cabang_id' => $this->cabang->id,
            'kode_poli' => 'PGT',
            'nama_poli' => 'Poli Gigi',
            'deskripsi' => 'Poli pertama',
            'status' => true,
        ]);

        $payload = [
            'kode_poli' => 'PGT',
            'nama_poli' => 'Poli Gigi Lainnya',
            'deskripsi' => 'Mencoba tambah poli dengan kode sama',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.poli.store'), $payload);

        $response->assertSessionHasErrors(['kode_poli']);
    }

    /**
     * Skenario Exception: Input wajib (kode_poli & nama_poli) kosong ditolak.
     */
    public function test_exception_input_wajib_kosong_ditolak(): void
    {
        $payload = [
            'kode_poli' => '',
            'nama_poli' => '',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.poli.store'), $payload);

        $response->assertSessionHasErrors(['kode_poli', 'nama_poli']);
    }

    /**
     * Skenario Exception: User non-admin (pasien) tidak diizinkan mengakses data poli admin.
     */
    public function test_exception_bukan_admin_tidak_dapat_mengakses_data_poli(): void
    {
        $pasien = User::factory()->create([
            'role' => 'pasien',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pasien)->get(route('admin.poli.index'));

        $response->assertForbidden();
    }
}
