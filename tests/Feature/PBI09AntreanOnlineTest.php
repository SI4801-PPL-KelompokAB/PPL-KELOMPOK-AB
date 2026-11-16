<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\KlinikCabang;
use App\Models\PatientProfile;
use App\Models\Poli;
use App\Models\Queue;
use App\Models\User;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PBI09AntreanOnlineTest extends TestCase
{
    use RefreshDatabase;

    protected User $pasien;

    protected PatientProfile $profil;

    protected User $pasienLain;

    protected PatientProfile $profilLain;

    protected Doctor $doctor;

    protected Poli $poli;

    protected DoctorSchedule $schedule;

    protected string $tanggalValid;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Cabang & Poli
        $cabang = KlinikCabang::create([
            'nama_cabang' => 'Klinik Bonamu Pusat',
            'alamat' => 'Jl. Sukabirus No. 10',
            'nomor_telepon' => '0229876543',
            'status' => true,
        ]);

        $this->poli = Poli::create([
            'klinik_cabang_id' => $cabang->id,
            'nama_poli' => 'Poli Gigi',
            'status' => true,
        ]);

        // Setup Dokter
        $dokterUser = User::create([
            'name' => 'drg. Budi Rahardjo',
            'email' => 'drg.budi@bonamu.test',
            'no_hp' => '081234567800',
            'role' => 'dokter',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $dokterUser->id,
            'nomor_sip' => 'SIP-GIGI-001',
        ]);

        // Hitung tanggal valid (misal besok atau hari ini)
        $besok = Carbon::tomorrow();
        $namaHariBesok = QueueService::hariDariTanggal($besok->toDateString());
        $this->tanggalValid = $besok->toDateString();

        // Setup Jadwal Praktik Dokter pada hari tersebut
        $this->schedule = DoctorSchedule::create([
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => $namaHariBesok,
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '13:00:00',
            'kuota' => 2, // Kuota kecil (2) untuk memudahkan uji batas kuota
            'status' => true,
        ]);

        // Setup Pasien 1 (Profil Lengkap)
        $this->pasien = User::create([
            'name' => 'Ahmad Santoso',
            'email' => 'ahmad@bonamu.test',
            'no_hp' => '081234567801',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->profil = PatientProfile::create([
            'user_id' => $this->pasien->id,
            'tanggal_lahir' => '1995-05-15',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Radio Palasari No. 5',
        ]);

        // Setup Pasien 2 (Profil Lengkap)
        $this->pasienLain = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@bonamu.test',
            'no_hp' => '081234567802',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->profilLain = PatientProfile::create([
            'user_id' => $this->pasienLain->id,
            'tanggal_lahir' => '1998-08-20',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Sukapura Indah No. 2',
        ]);
    }

    /**
     * Skenario 1 (Normal): Pasien dapat melihat halaman pendaftaran antrean online.
     */
    public function test_pasien_dapat_melihat_halaman_pendaftaran_antrean(): void
    {
        $response = $this->actingAs($this->pasien)->get(route('antrean.create', [
            'poli' => $this->poli->id,
            'tanggal' => $this->tanggalValid,
        ]));

        $response->assertStatus(200);
        $response->assertSee('drg. Budi Rahardjo');
        $response->assertSee('Poli Gigi');
        $response->assertSee('Ambil Nomor Antrean');
    }

    /**
     * Skenario 2 (Normal): Pasien berhasil membuat antrean online dan mendapatkan nomor antrean 1.
     */
    public function test_pasien_berhasil_membuat_antrean_online_pertama(): void
    {
        $payload = [
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
        ];

        $response = $this->actingAs($this->pasien)->post(route('antrean.store'), $payload);

        $queue = Queue::where('patient_profile_id', $this->profil->id)->first();
        $this->assertNotNull($queue);
        $this->assertEquals(1, $queue->nomor_antrean);
        $this->assertEquals(Queue::STATUS_WAITING, $queue->status);

        $response->assertRedirect(route('antrean.show', $queue));
        $response->assertSessionHas('success');
    }

    /**
     * Skenario 3 (Normal): Pasien kedua mendapatkan nomor antrean berikutnya (nomor 2) secara berurutan.
     */
    public function test_penomoran_antrean_berurutan_per_dokter_per_tanggal(): void
    {
        // Antrean 1 oleh Ahmad
        Queue::create([
            'patient_profile_id' => $this->profil->id,
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
            'nomor_antrean' => 1,
            'status' => Queue::STATUS_COMPLETED, // selesai agar tidak blokir antrean aktif
        ]);

        // Antrean 2 oleh Dewi
        $payload = [
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
        ];

        $response = $this->actingAs($this->pasienLain)->post(route('antrean.store'), $payload);

        $queueDewi = Queue::where('patient_profile_id', $this->profilLain->id)->first();
        $this->assertNotNull($queueDewi);
        $this->assertEquals(2, $queueDewi->nomor_antrean);
        $response->assertRedirect(route('antrean.show', $queueDewi));
    }

    /**
     * Skenario 4 (Normal): Pasien dapat melihat tiket bukti antrean miliknya.
     */
    public function test_pasien_dapat_melihat_tiket_antrean_miliknya(): void
    {
        $queue = Queue::create([
            'patient_profile_id' => $this->profil->id,
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
            'nomor_antrean' => 1,
            'status' => Queue::STATUS_WAITING,
        ]);

        $response = $this->actingAs($this->pasien)->get(route('antrean.show', $queue));

        $response->assertStatus(200);
        $response->assertSee('001');
        $response->assertSee('drg. Budi Rahardjo');
        $response->assertSee('Poli Gigi');
        $response->assertSee('Menunggu');
    }

    /**
     * Skenario 5 (Exception): Ditolak jika profil identitas pasien belum lengkap.
     */
    public function test_pembuatan_antrean_ditolak_jika_profil_belum_lengkap(): void
    {
        // Pasien dengan profil kosong
        $pasienBaru = User::create([
            'name' => 'Pasien Tanpa Profil',
            'email' => 'tanpa.profil@bonamu.test',
            'no_hp' => '081234567899',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        PatientProfile::create([
            'user_id' => $pasienBaru->id,
            'tanggal_lahir' => null,
            'alamat' => '',
        ]);

        $payload = [
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
        ];

        $response = $this->actingAs($pasienBaru)->post(route('antrean.store'), $payload);

        $response->assertSessionHasErrors(['antrean']);
        $this->assertDatabaseMissing('queues', [
            'patient_profile_id' => $pasienBaru->patientProfile->id,
        ]);
    }

    /**
     * Skenario 6 (Exception): Ditolak jika tanggal antrean sudah lewat.
     */
    public function test_pembuatan_antrean_ditolak_jika_tanggal_sudah_lewat(): void
    {
        $tanggalKemarin = Carbon::yesterday()->toDateString();

        $payload = [
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $tanggalKemarin,
        ];

        $response = $this->actingAs($this->pasien)->post(route('antrean.store'), $payload);

        $response->assertSessionHasErrors(['antrean']);
        $this->assertDatabaseMissing('queues', [
            'tanggal_antrean' => $tanggalKemarin,
        ]);
    }

    /**
     * Skenario 7 (Exception): Ditolak jika dokter tidak memiliki jadwal pada hari dari tanggal yang dipilih.
     */
    public function test_pembuatan_antrean_ditolak_jika_dokter_tidak_praktik_di_hari_tersebut(): void
    {
        // Cari tanggal 2 hari lagi yang harinya berbeda dengan jadwal dokter
        $duaHariLagi = Carbon::tomorrow()->addDay();
        while (QueueService::hariDariTanggal($duaHariLagi->toDateString()) === $this->schedule->hari) {
            $duaHariLagi->addDay();
        }

        $payload = [
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $duaHariLagi->toDateString(),
        ];

        $response = $this->actingAs($this->pasien)->post(route('antrean.store'), $payload);

        $response->assertSessionHasErrors(['antrean']);
    }

    /**
     * Skenario 8 (Exception): Ditolak jika pasien sudah memiliki antrean aktif (menolak antrean aktif ganda).
     */
    public function test_pembuatan_antrean_ditolak_jika_pasien_sudah_memiliki_antrean_aktif(): void
    {
        // Pasien sudah punya antrean berstatus 'waiting'
        Queue::create([
            'patient_profile_id' => $this->profil->id,
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
            'nomor_antrean' => 1,
            'status' => Queue::STATUS_WAITING,
        ]);

        // Mencoba membuat antrean baru lagi
        $payload = [
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
        ];

        $response = $this->actingAs($this->pasien)->post(route('antrean.store'), $payload);

        $response->assertSessionHasErrors(['antrean']);
        $this->assertEquals(1, Queue::where('patient_profile_id', $this->profil->id)->count());
    }

    /**
     * Skenario 9 (Exception): Ditolak jika kuota dokter pada tanggal tersebut sudah habis (Jadwal Penuh).
     */
    public function test_pembuatan_antrean_ditolak_jika_kuota_penuh(): void
    {
        // Kuota jadwal adalah 2
        // Isi kuota sampai penuh dengan antrean non-aktif pasien lain agar tidak bentrok antrean aktif
        Queue::create([
            'patient_profile_id' => $this->profilLain->id,
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
            'nomor_antrean' => 1,
            'status' => Queue::STATUS_COMPLETED,
        ]);

        $userKetiga = User::create([
            'name' => 'User Ketiga',
            'email' => 'ketiga@bonamu.test',
            'no_hp' => '081234567803',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $profilKetiga = PatientProfile::create([
            'user_id' => $userKetiga->id,
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Bandung',
        ]);
        Queue::create([
            'patient_profile_id' => $profilKetiga->id,
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
            'nomor_antrean' => 2,
            'status' => Queue::STATUS_COMPLETED,
        ]);

        // Sekarang total antrean = 2 (kuota penuh). Ahmad mencoba mendaftar.
        $payload = [
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
        ];

        $response = $this->actingAs($this->pasien)->post(route('antrean.store'), $payload);

        $response->assertSessionHasErrors(['antrean']);
        $this->assertDatabaseMissing('queues', [
            'patient_profile_id' => $this->profil->id,
        ]);
    }

    /**
     * Skenario 10 (Exception): Pasien lain tidak boleh membuka tiket antrean milik orang lain (403).
     */
    public function test_pasien_lain_tidak_boleh_melihat_tiket_orang_lain(): void
    {
        $queue = Queue::create([
            'patient_profile_id' => $this->profil->id,
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_antrean' => $this->tanggalValid,
            'nomor_antrean' => 1,
            'status' => Queue::STATUS_WAITING,
        ]);

        // Pasien lain mencoba melihat tiket
        $response = $this->actingAs($this->pasienLain)->get(route('antrean.show', $queue));

        $response->assertStatus(403);
    }
}
