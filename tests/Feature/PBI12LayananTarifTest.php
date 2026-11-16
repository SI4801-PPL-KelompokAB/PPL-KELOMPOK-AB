<?php

namespace Tests\Feature;

use App\Models\KlinikCabang;
use App\Models\Layanan;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PBI12LayananTarifTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pasien;
    private KlinikCabang $cabang;
    private Poli $poli;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang = KlinikCabang::create([
            'nama_cabang' => 'Klinik Bona Mitra Keluarga Pusat',
            'alamat' => 'Jl. Sukabirus No. 12, Bandung',
            'nomor_telepon' => '0221234567',
            'status' => true,
        ]);

        $this->poli = Poli::create([
            'klinik_cabang_id' => $this->cabang->id,
            'kode_poli' => 'PGT',
            'nama_poli' => 'Poli Gigi dan Mulut',
            'deskripsi' => 'Layanan perawatan gigi dan mulut',
            'status' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->pasien = User::factory()->create([
            'role' => 'pasien',
            'is_active' => true,
        ]);
    }

    /**
     * Skenario Normal: Admin dapat melihat daftar layanan & tarif.
     */
    public function test_normal_admin_dapat_melihat_daftar_layanan_dan_tarif(): void
    {
        $layanan = Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'poli_id' => $this->poli->id,
            'kode_layanan' => 'LAY-GGI-01',
            'nama_layanan' => 'Pembersihan Karang Gigi (Scaling)',
            'tarif_umum' => 150000,
            'tarif_bpjs' => 0,
            'deskripsi' => 'Scaling gigi rahang atas dan bawah',
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.layanan.index'));

        $response->assertOk();
        $response->assertViewIs('admin.layanan.index');
        $response->assertSee('Pembersihan Karang Gigi (Scaling)');
        $response->assertSee('LAY-GGI-01');
        $response->assertSee('Rp 150.000');
    }

    /**
     * Skenario Normal: Admin dapat menambah layanan dan tarif baru.
     */
    public function test_normal_admin_dapat_menambah_layanan_baru(): void
    {
        $payload = [
            'klinik_cabang_id' => $this->cabang->id,
            'poli_id' => $this->poli->id,
            'kode_layanan' => 'LAY-GGI-02',
            'nama_layanan' => 'Tambal Gigi Komposit',
            'tarif_umum' => 200000,
            'tarif_bpjs' => 0,
            'deskripsi' => 'Penambalan gigi berlubang',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.layanan.store'), $payload);

        $response->assertRedirect(route('admin.layanan.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('layanans', [
            'nama_layanan' => 'Tambal Gigi Komposit',
            'kode_layanan' => 'LAY-GGI-02',
            'tarif_umum' => 200000,
            'tarif_bpjs' => 0,
            'status' => 1,
        ]);
    }

    /**
     * Skenario Normal: Admin dapat mengubah data layanan dan tarif.
     */
    public function test_normal_admin_dapat_mengubah_data_layanan_dan_tarif(): void
    {
        $layanan = Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'poli_id' => $this->poli->id,
            'kode_layanan' => 'LAY-OLD',
            'nama_layanan' => 'Pemeriksaan Awal',
            'tarif_umum' => 45000,
            'tarif_bpjs' => 0,
            'status' => true,
        ]);

        $payload = [
            'nama_layanan' => 'Pemeriksaan Kesehatan Umum Lengkap',
            'kode_layanan' => 'LAY-UMM-01',
            'poli_id' => $this->poli->id,
            'tarif_umum' => 60000,
            'tarif_bpjs' => 10000,
            'deskripsi' => 'Pemeriksaan fisik + tensi + konsultasi dokter',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.layanan.update', $layanan), $payload);

        $response->assertRedirect(route('admin.layanan.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('layanans', [
            'id' => $layanan->id,
            'nama_layanan' => 'Pemeriksaan Kesehatan Umum Lengkap',
            'kode_layanan' => 'LAY-UMM-01',
            'tarif_umum' => 60000,
            'tarif_bpjs' => 10000,
        ]);
    }

    /**
     * Skenario Normal: Admin dapat toggle status aktif/nonaktif layanan (soft-delete).
     */
    public function test_normal_admin_dapat_toggle_status_layanan(): void
    {
        $layanan = Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'poli_id' => $this->poli->id,
            'nama_layanan' => 'Suntik Vitamin C',
            'tarif_umum' => 75000,
            'tarif_bpjs' => 75000,
            'status' => true,
        ]);

        // Toggle 1: Aktif -> Nonaktif
        $response1 = $this->actingAs($this->admin)
            ->patch(route('admin.layanan.toggle-status', $layanan));

        $response1->assertRedirect(route('admin.layanan.index'));
        $this->assertDatabaseHas('layanans', [
            'id' => $layanan->id,
            'status' => false,
        ]);

        // Toggle 2: Nonaktif -> Aktif kembali
        $response2 = $this->actingAs($this->admin)
            ->patch(route('admin.layanan.toggle-status', $layanan));

        $response2->assertRedirect(route('admin.layanan.index'));
        $this->assertDatabaseHas('layanans', [
            'id' => $layanan->id,
            'status' => true,
        ]);
    }

    /**
     * Skenario Normal: Admin dapat melakukan sorting layanan.
     */
    public function test_normal_admin_dapat_melakukan_sorting_layanan(): void
    {
        Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'nama_layanan' => 'Layanan B',
            'tarif_umum' => 100000,
            'tarif_bpjs' => 0,
            'status' => true,
        ]);

        Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'nama_layanan' => 'Layanan A',
            'tarif_umum' => 50000,
            'tarif_bpjs' => 0,
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.layanan.index', ['sort' => 'nama_layanan', 'dir' => 'asc']));

        $response->assertOk();
        $response->assertSeeInOrder(['Layanan A', 'Layanan B']);
    }

    /**
     * Skenario Exception: Tambah layanan dengan nama duplikat di cabang yang sama ditolak.
     */
    public function test_exception_tambah_layanan_nama_duplikat_di_cabang_sama_ditolak(): void
    {
        Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'nama_layanan' => 'Cabut Gigi Dewasa',
            'tarif_umum' => 120000,
            'tarif_bpjs' => 0,
            'status' => true,
        ]);

        $payload = [
            'nama_layanan' => 'Cabut Gigi Dewasa', // Duplikat
            'tarif_umum' => 130000,
            'tarif_bpjs' => 0,
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.layanan.store'), $payload);

        $response->assertSessionHasErrors(['nama_layanan']);
        $this->assertDatabaseCount('layanans', 1);
    }

    /**
     * Skenario Exception: Tarif bernilai negatif ditolak.
     */
    public function test_exception_tarif_negatif_ditolak(): void
    {
        $payload = [
            'nama_layanan' => 'Layanan Uji Negatif',
            'tarif_umum' => -5000, // Invalid negatif
            'tarif_bpjs' => -1000, // Invalid negatif
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.layanan.store'), $payload);

        $response->assertSessionHasErrors(['tarif_umum', 'tarif_bpjs']);
    }

    /**
     * Skenario Exception: Field wajib kosong ditolak.
     */
    public function test_exception_field_wajib_kosong_ditolak(): void
    {
        $payload = [
            'nama_layanan' => '',
            'tarif_umum' => '',
            'tarif_bpjs' => '',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.layanan.store'), $payload);

        $response->assertSessionHasErrors(['nama_layanan', 'tarif_umum', 'tarif_bpjs']);
    }

    /**
     * Skenario Exception: Update dengan nama yang sudah dipakai layanan lain ditolak.
     */
    public function test_exception_edit_layanan_nama_duplikat_milik_lain_ditolak(): void
    {
        $layanan1 = Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'nama_layanan' => 'Layanan Utama',
            'tarif_umum' => 50000,
            'tarif_bpjs' => 0,
            'status' => true,
        ]);

        $layanan2 = Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'nama_layanan' => 'Layanan Kedua',
            'tarif_umum' => 75000,
            'tarif_bpjs' => 0,
            'status' => true,
        ]);

        $payload = [
            'nama_layanan' => 'Layanan Utama', // Menggunakan nama layanan 1
            'tarif_umum' => 80000,
            'tarif_bpjs' => 0,
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.layanan.update', $layanan2), $payload);

        $response->assertSessionHasErrors(['nama_layanan']);
    }

    /**
     * Skenario Normal: Update data layanan dengan nama sama milik sendiri diizinkan.
     */
    public function test_normal_edit_layanan_nama_sama_sendiri_diizinkan(): void
    {
        $layanan = Layanan::create([
            'klinik_cabang_id' => $this->cabang->id,
            'nama_layanan' => 'Layanan Tetap',
            'tarif_umum' => 50000,
            'tarif_bpjs' => 0,
            'status' => true,
        ]);

        $payload = [
            'nama_layanan' => 'Layanan Tetap',
            'tarif_umum' => 95000, // Update tarif saja
            'tarif_bpjs' => 15000,
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.layanan.update', $layanan), $payload);

        $response->assertRedirect(route('admin.layanan.index'));
        $this->assertDatabaseHas('layanans', [
            'id' => $layanan->id,
            'tarif_umum' => 95000,
            'tarif_bpjs' => 15000,
        ]);
    }

    /**
     * Skenario Otorisasi: Guest dan Pasien dilarang mengakses kelola layanan.
     */
    public function test_otorisasi_guest_dan_pasien_dilarang_akses(): void
    {
        // Guest
        $resGuest = $this->get(route('admin.layanan.index'));
        $resGuest->assertRedirect(route('login'));

        // Pasien
        $resPasien = $this->actingAs($this->pasien)->get(route('admin.layanan.index'));
        $resPasien->assertForbidden();
    }
}
