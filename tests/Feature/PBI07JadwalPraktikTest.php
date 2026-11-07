<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\KlinikCabang;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PBI07JadwalPraktikTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $pasien;

    protected Doctor $doctor;

    protected Poli $poli;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Admin
        $this->admin = User::create([
            'name' => 'Admin Bonamu',
            'email' => 'admin@bonamu.test',
            'no_hp' => '081234567890',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Setup Pasien
        $this->pasien = User::create([
            'name' => 'Pasien Bonamu',
            'email' => 'pasien@bonamu.test',
            'no_hp' => '081234567891',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Setup Cabang & Poli
        $cabang = KlinikCabang::create([
            'nama_cabang' => 'Klinik Bonamu Bojongsoang',
            'alamat' => 'Jl. Bojongsoang No. 1',
            'nomor_telepon' => '0221234567',
            'status' => true,
        ]);

        $this->poli = Poli::create([
            'klinik_cabang_id' => $cabang->id,
            'nama_poli' => 'Poli Umum',
            'status' => true,
        ]);

        // Setup Dokter
        $dokterUser = User::create([
            'name' => 'dr. Siti Rahma',
            'email' => 'siti.rahma@bonamu.test',
            'no_hp' => '081234567892',
            'role' => 'dokter',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $dokterUser->id,
            'nomor_sip' => 'SIP-001-2026',
        ]);
    }

    /**
     * Skenario 1 (Normal): Admin berhasil melihat daftar jadwal dokter.
     */
    public function test_admin_bisa_melihat_daftar_jadwal(): void
    {
        DoctorSchedule::create([
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
            'kuota' => 20,
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index'));

        $response->assertStatus(200);
        $response->assertSee('dr. Siti Rahma');
        $response->assertSee('Poli Umum');
        $response->assertSee('Senin');
        $response->assertSee('20 pasien');
    }

    /**
     * Skenario 2 (Normal): Admin berhasil membuat jadwal praktik dokter baru.
     */
    public function test_admin_berhasil_membuat_jadwal_praktik_baru(): void
    {
        $payload = [
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '11:00',
            'kuota' => 15,
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), $payload);

        $response->assertRedirect(route('admin.jadwal.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('doctor_schedules', [
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'senin',
            'kuota' => 15,
            'status' => 1,
        ]);
    }

    /**
     * Skenario 3 (Normal): Admin berhasil mengedit jadwal dokter yang ada.
     */
    public function test_admin_berhasil_mengedit_jadwal_praktik(): void
    {
        $schedule = DoctorSchedule::create([
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'selasa',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '12:00:00',
            'kuota' => 10,
            'status' => true,
        ]);

        $payload = [
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'selasa',
            'jam_mulai' => '09:30',
            'jam_selesai' => '13:00',
            'kuota' => 25,
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $schedule), $payload);

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('doctor_schedules', [
            'id' => $schedule->id,
            'kuota' => 25,
        ]);
    }

    /**
     * Skenario 4 (Normal): Admin menonaktifkan jadwal dokter (tidak dihapus dari DB).
     */
    public function test_admin_dapat_menonaktifkan_jadwal_tanpa_menghapus(): void
    {
        $schedule = DoctorSchedule::create([
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'rabu',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '11:00:00',
            'kuota' => 10,
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.jadwal.destroy', $schedule));

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('doctor_schedules', [
            'id' => $schedule->id,
            'status' => false,
        ]);
    }

    /**
     * Skenario 5 (Exception): Ditolak jika jam selesai tidak setelah jam mulai.
     */
    public function test_pembuatan_jadwal_ditolak_jika_jam_selesai_lebih_awal(): void
    {
        $payload = [
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'kamis',
            'jam_mulai' => '14:00',
            'jam_selesai' => '10:00', // Jam selesai lebih awal
            'kuota' => 10,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), $payload);

        $response->assertSessionHasErrors(['jam_selesai']);
        $this->assertDatabaseMissing('doctor_schedules', [
            'doctor_id' => $this->doctor->id,
            'hari' => 'kamis',
        ]);
    }

    /**
     * Skenario 6 (Exception): Ditolak jika kuota kurang dari 1.
     */
    public function test_pembuatan_jadwal_ditolak_jika_kuota_nol(): void
    {
        $payload = [
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'jumat',
            'jam_mulai' => '08:00',
            'jam_selesai' => '11:00',
            'kuota' => 0, // Kuota kurang dari 1
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), $payload);

        $response->assertSessionHasErrors(['kuota']);
    }

    /**
     * Skenario 7 (Exception): Ditolak jika jam bertabrakan (bentrok) dengan jadwal aktif dokter yang sama pada hari yang sama.
     */
    public function test_pembuatan_jadwal_ditolak_jika_jam_bentrok(): void
    {
        // Buat jadwal yang sudah ada: 08:00 - 12:00
        DoctorSchedule::create([
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'sabtu',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
            'kuota' => 20,
            'status' => true,
        ]);

        // Coba masukkan jadwal yang bertabrakan: 10:00 - 14:00
        $payload = [
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => 'sabtu',
            'jam_mulai' => '10:00',
            'jam_selesai' => '14:00',
            'kuota' => 10,
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), $payload);

        $response->assertSessionHasErrors(['jadwal']);
        $this->assertEquals(1, DoctorSchedule::where('doctor_id', $this->doctor->id)->where('hari', 'sabtu')->count());
    }

    /**
     * Skenario 8 (Exception): Akses route admin ditolak (403) jika bukan role admin.
     */
    public function test_pasien_tidak_bisa_mengakses_jadwal_admin(): void
    {
        $response = $this->actingAs($this->pasien)->get(route('admin.jadwal.index'));

        $response->assertStatus(403);
    }
}
