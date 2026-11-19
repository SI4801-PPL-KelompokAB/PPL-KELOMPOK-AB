<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\ConsultationMessage;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\KlinikCabang;
use App\Models\PatientProfile;
use App\Models\Poli;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI11 - Menangani Konsultasi Online (SPK-31)
 * Mengikuti test case scenario TC-01 sampai TC-16.
 */
class PBI11MenanganiKonsultasiOnlineTest extends TestCase
{
    use RefreshDatabase;

    protected User $dokter;

    protected User $dokterLain;

    protected User $pasien;

    protected PatientProfile $profil;

    protected Doctor $doctor;

    protected Doctor $doctorLain;

    protected DoctorSchedule $schedule;

    protected DoctorSchedule $scheduleLain;

    protected Consultation $konsultasi;

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

        $poli = Poli::create([
            'klinik_cabang_id' => $cabang->id,
            'nama_poli' => 'Poli Umum',
            'status' => true,
        ]);

        // Setup dr. Andi (pemilik konsultasi)
        $this->dokter = User::forceCreate([
            'name' => 'dr. Andi Pratama',
            'email' => 'dr.andi@bonamu.test',
            'no_hp' => '081234567820',
            'role' => 'dokter',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->dokter->id,
            'nomor_sip' => 'SIP-UMUM-001',
        ]);

        // Setup dr. Sari (dokter lain)
        $this->dokterLain = User::forceCreate([
            'name' => 'dr. Sari Wulandari',
            'email' => 'dr.sari@bonamu.test',
            'no_hp' => '081234567821',
            'role' => 'dokter',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->doctorLain = Doctor::create([
            'user_id' => $this->dokterLain->id,
            'nomor_sip' => 'SIP-UMUM-002',
        ]);

        // Jadwal praktik besok untuk kedua dokter
        $besok = Carbon::tomorrow();
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'][$besok->dayOfWeekIso - 1];

        $this->schedule = DoctorSchedule::create([
            'doctor_id' => $this->doctor->id,
            'poli_id' => $poli->id,
            'hari' => $hari,
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '13:00:00',
            'kuota' => 10,
            'status' => true,
        ]);

        $this->scheduleLain = DoctorSchedule::create([
            'doctor_id' => $this->doctorLain->id,
            'poli_id' => $poli->id,
            'hari' => $hari,
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '13:00:00',
            'kuota' => 10,
            'status' => true,
        ]);

        // Setup Pasien
        $this->pasien = User::forceCreate([
            'name' => 'Budi Santoso',
            'email' => 'budi@bonamu.test',
            'no_hp' => '081234567822',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->profil = PatientProfile::create([
            'user_id' => $this->pasien->id,
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Telekomunikasi No. 1',
        ]);

        // Konsultasi berstatus Menunggu (pending) dari Budi untuk dr. Andi
        $this->konsultasi = $this->buatKonsultasi($this->schedule, 1, 'pending');
    }

    /** Membuat konsultasi pada jadwal tertentu dengan status tertentu. */
    private function buatKonsultasi(DoctorSchedule $jadwal, int $urut, string $status, string $keluhan = 'Demam dan batuk sejak 2 hari'): Consultation
    {
        return Consultation::create([
            'nomor_konsultasi' => 'KON-'.Carbon::tomorrow()->format('Ymd').'-'.str_pad($urut, 4, '0', STR_PAD_LEFT),
            'patient_profile_id' => $this->profil->id,
            'doctor_id' => $jadwal->doctor_id,
            'doctor_schedule_id' => $jadwal->id,
            'tanggal_konsultasi' => Carbon::tomorrow()->toDateString(),
            'subject' => $keluhan,
            'keluhan' => $keluhan,
            'status' => $status,
        ]);
    }

    /**
     * TC-01 (Normal): Dokter membuka daftar konsultasi masuk.
     * Output: hanya konsultasi untuk dr. Andi, urut Menunggu -> Berlangsung -> Selesai, kartu jumlah per status sesuai.
     */
    public function test_normal_tc01_dokter_membuka_daftar_konsultasi_masuk(): void
    {
        $selesai = $this->buatKonsultasi($this->schedule, 2, 'completed', 'Sakit kepala sebelah kanan');
        $berlangsung = $this->buatKonsultasi($this->schedule, 3, 'ongoing', 'Mual dan nyeri lambung');
        $milikSari = $this->buatKonsultasi($this->scheduleLain, 4, 'pending', 'Gatal-gatal di tangan');

        $response = $this->actingAs($this->dokter)->get(route('dokter.konsultasi.index'));

        $response->assertStatus(200);

        // Data yang tampil: nomor, pasien, poli, keluhan, status
        $response->assertSee($this->konsultasi->nomor_konsultasi);
        $response->assertSee('Budi Santoso');
        $response->assertSee('Poli Umum');
        $response->assertSee('Demam dan batuk sejak 2 hari');

        // Hanya konsultasi milik dr. Andi
        $response->assertDontSee($milikSari->nomor_konsultasi);
        $response->assertDontSee('Gatal-gatal di tangan');

        // Urutan Menunggu -> Berlangsung -> Selesai
        $response->assertSeeInOrder([
            $this->konsultasi->nomor_konsultasi,
            $berlangsung->nomor_konsultasi,
            $selesai->nomor_konsultasi,
        ]);

        // Kartu jumlah per status (masing-masing 1)
        $hitung = $response->viewData('hitung');
        $this->assertEquals(1, $hitung['pending']);
        $this->assertEquals(1, $hitung['ongoing']);
        $this->assertEquals(1, $hitung['completed']);
    }

    /**
     * TC-02 (Normal): Dokter memulai sesi pada konsultasi berstatus Menunggu.
     * Output: status menjadi Berlangsung (ongoing), pindah ke Ruang Konsultasi, muncul "Sesi dimulai."
     */
    public function test_normal_tc02_dokter_memulai_sesi(): void
    {
        $response = $this->actingAs($this->dokter)->post(route('dokter.konsultasi.mulai', $this->konsultasi));

        $this->assertEquals('ongoing', $this->konsultasi->fresh()->status);
        $response->assertRedirect(route('konsultasi.ruang', $this->konsultasi));
        $response->assertSessionHas('sukses', 'Sesi dimulai.');

        $this->actingAs($this->dokter)->get(route('konsultasi.ruang', $this->konsultasi))->assertSee('Sesi dimulai.');
    }

    /**
     * TC-03 (Normal): Dokter mengirim pesan saat sesi berlangsung.
     * Output: tersimpan di consultation_messages (sender = dr. Andi), tampil dengan label "Anda",
     * dan pasien melihatnya dengan nama dr. Andi.
     */
    public function test_normal_tc03_dokter_mengirim_pesan_saat_sesi_berlangsung(): void
    {
        $this->konsultasi->update(['status' => 'ongoing']);

        $response = $this->actingAs($this->dokter)->post(route('konsultasi.pesan', $this->konsultasi), [
            'pesan' => 'Halo, sudah minum obat?',
        ]);

        $response->assertRedirect(route('konsultasi.ruang', $this->konsultasi));
        $this->assertDatabaseHas('consultation_messages', [
            'consultation_id' => $this->konsultasi->id,
            'sender_user_id' => $this->dokter->id,
            'message' => 'Halo, sudah minum obat?',
        ]);

        // Dokter melihat pesannya dengan label "Anda"
        $this->actingAs($this->dokter)->get(route('konsultasi.ruang', $this->konsultasi))
            ->assertSeeInOrder(['Anda', 'Halo, sudah minum obat?']);

        // Pasien melihat pesan dengan nama dr. Andi
        $this->actingAs($this->pasien)->get(route('konsultasi.ruang', $this->konsultasi))
            ->assertSeeInOrder(['dr. Andi Pratama', 'Halo, sudah minum obat?']);
    }

    /**
     * TC-04 (Normal): Dokter mengakhiri sesi.
     * Output: status menjadi Selesai (completed), kembali ke daftar, muncul "Sesi diakhiri.", tombol berubah jadi "Lihat".
     */
    public function test_normal_tc04_dokter_mengakhiri_sesi(): void
    {
        $this->konsultasi->update(['status' => 'ongoing']);

        $response = $this->actingAs($this->dokter)->post(route('dokter.konsultasi.selesai', $this->konsultasi));

        $this->assertEquals('completed', $this->konsultasi->fresh()->status);
        $response->assertRedirect(route('dokter.konsultasi.index'));
        $response->assertSessionHas('sukses', 'Sesi diakhiri.');

        $this->actingAs($this->dokter)->get(route('dokter.konsultasi.index'))
            ->assertSee('Sesi diakhiri.')
            ->assertSee('Lihat')
            ->assertDontSee('Mulai Sesi');
    }

    /**
     * TC-05 (Normal): Dokter membuka riwayat konsultasi yang sudah selesai.
     * Output: keluhan awal dan riwayat chat tampil, form pesan disembunyikan, muncul "Sesi telah berakhir."
     */
    public function test_normal_tc05_dokter_membuka_riwayat_konsultasi_selesai(): void
    {
        ConsultationMessage::create([
            'consultation_id' => $this->konsultasi->id,
            'sender_user_id' => $this->dokter->id,
            'message' => 'Istirahat yang cukup dan banyak minum air putih.',
        ]);
        $this->konsultasi->update(['status' => 'completed']);

        $response = $this->actingAs($this->dokter)->get(route('konsultasi.ruang', $this->konsultasi));

        $response->assertStatus(200);
        $response->assertSee('Demam dan batuk sejak 2 hari');
        $response->assertSee('Istirahat yang cukup dan banyak minum air putih.');
        $response->assertSee('Sesi telah berakhir.');
        $response->assertDontSee('name="pesan"', false);
    }

    /**
     * TC-06 (Exception): Dokter mengirim pesan kosong.
     * Output: pesan tidak tersimpan, muncul "Pesan wajib diisi."
     */
    public function test_exception_tc06_kirim_pesan_kosong_ditolak(): void
    {
        $this->konsultasi->update(['status' => 'ongoing']);

        $response = $this->actingAs($this->dokter)->post(route('konsultasi.pesan', $this->konsultasi), [
            'pesan' => '',
        ]);

        $response->assertSessionHasErrors(['pesan' => 'Pesan wajib diisi.']);
        $this->assertDatabaseCount('consultation_messages', 0);
    }

    /**
     * TC-07 (Exception): Dokter mengirim pesan terlalu panjang (1001 karakter).
     * Output: pesan tidak tersimpan, muncul "Pesan maksimal 1000 karakter."
     */
    public function test_exception_tc07_kirim_pesan_lebih_dari_1000_karakter_ditolak(): void
    {
        $this->konsultasi->update(['status' => 'ongoing']);

        // Karakter terakhir huruf, bukan spasi, agar tidak terpotong oleh trim Laravel
        $pesan1001 = substr(str_repeat('Minum obat tiga kali sehari. ', 40), 0, 1001);
        $this->assertEquals(1001, strlen(trim($pesan1001)));

        $response = $this->actingAs($this->dokter)->post(route('konsultasi.pesan', $this->konsultasi), [
            'pesan' => $pesan1001,
        ]);

        $response->assertSessionHasErrors(['pesan' => 'Pesan maksimal 1000 karakter.']);
        $this->assertDatabaseCount('consultation_messages', 0);
    }

    /**
     * TC-08 (Exception): Mengirim pesan sebelum sesi dimulai (status pending).
     * Output: form pesan tidak tampil ("Menunggu dokter memulai sesi."), request ditolak
     * dengan "Pesan hanya dapat dikirim saat sesi berlangsung."
     */
    public function test_exception_tc08_kirim_pesan_sebelum_sesi_dimulai_ditolak(): void
    {
        $this->actingAs($this->dokter)->get(route('konsultasi.ruang', $this->konsultasi))
            ->assertSee('Menunggu dokter memulai sesi.')
            ->assertDontSee('name="pesan"', false);

        $response = $this->actingAs($this->dokter)->post(route('konsultasi.pesan', $this->konsultasi), [
            'pesan' => 'Halo, sudah minum obat?',
        ]);

        $response->assertSessionHasErrors(['pesan' => 'Pesan hanya dapat dikirim saat sesi berlangsung.']);
        $this->assertDatabaseCount('consultation_messages', 0);
    }

    /**
     * TC-09 (Exception): Mengirim pesan setelah sesi berakhir (status completed).
     * Output: pesan tidak tersimpan, muncul "Pesan hanya dapat dikirim saat sesi berlangsung."
     */
    public function test_exception_tc09_kirim_pesan_setelah_sesi_berakhir_ditolak(): void
    {
        $this->konsultasi->update(['status' => 'completed']);

        $response = $this->actingAs($this->dokter)->post(route('konsultasi.pesan', $this->konsultasi), [
            'pesan' => 'Halo, sudah minum obat?',
        ]);

        $response->assertSessionHasErrors(['pesan' => 'Pesan hanya dapat dikirim saat sesi berlangsung.']);
        $this->assertDatabaseCount('consultation_messages', 0);
    }

    /**
     * TC-10 (Exception): Memulai sesi yang sudah berlangsung atau selesai.
     * Output: status tidak berubah, muncul "Sesi hanya dapat dimulai dari status Menunggu."
     */
    public function test_exception_tc10_mulai_sesi_yang_sudah_berlangsung_atau_selesai_ditolak(): void
    {
        foreach (['ongoing', 'completed'] as $status) {
            $this->konsultasi->update(['status' => $status]);

            $response = $this->actingAs($this->dokter)->post(route('dokter.konsultasi.mulai', $this->konsultasi));

            $response->assertSessionHasErrors(['status' => 'Sesi hanya dapat dimulai dari status Menunggu.']);
            $this->assertEquals($status, $this->konsultasi->fresh()->status);
        }
    }

    /**
     * TC-11 (Exception): Mengakhiri sesi yang belum dimulai (status pending).
     * Output: status tidak berubah, muncul "Sesi hanya dapat diakhiri dari status Berlangsung."
     */
    public function test_exception_tc11_akhiri_sesi_yang_belum_dimulai_ditolak(): void
    {
        $response = $this->actingAs($this->dokter)->post(route('dokter.konsultasi.selesai', $this->konsultasi));

        $response->assertSessionHasErrors(['status' => 'Sesi hanya dapat diakhiri dari status Berlangsung.']);
        $this->assertEquals('pending', $this->konsultasi->fresh()->status);
    }

    /**
     * TC-12 (Exception): Dokter membuka atau memulai konsultasi milik dokter lain.
     * Output: akses ditolak (HTTP 403), status dan data tidak berubah.
     */
    public function test_exception_tc12_dokter_lain_ditolak_403(): void
    {
        $this->actingAs($this->dokterLain)->get(route('konsultasi.ruang', $this->konsultasi))->assertStatus(403);
        $this->actingAs($this->dokterLain)->post(route('dokter.konsultasi.mulai', $this->konsultasi))->assertStatus(403);

        $this->assertEquals('pending', $this->konsultasi->fresh()->status);
        $this->assertDatabaseCount('consultation_messages', 0);
    }

    /**
     * TC-13 (Exception): Request konsultasi yang tidak ada (consultation_id = 9999).
     * Output: request ditolak (HTTP 404), tidak ada data berubah.
     */
    public function test_exception_tc13_konsultasi_tidak_ada_404(): void
    {
        $this->actingAs($this->dokter)->get('/konsultasi/9999/ruang')->assertStatus(404);
        $this->actingAs($this->dokter)->post('/dokter/konsultasi/9999/mulai')->assertStatus(404);

        $this->assertEquals('pending', $this->konsultasi->fresh()->status);
    }

    /**
     * TC-14 (Exception): Pengguna belum login mengakses Konsultasi Masuk.
     * Output: akses ditolak. Dengan middleware auth, tamu dialihkan ke halaman login
     * sehingga halaman Konsultasi Masuk tidak bisa dibuka.
     */
    public function test_exception_tc14_pengguna_belum_login_ditolak(): void
    {
        $this->get(route('dokter.konsultasi.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * TC-15 (Exception): Pengguna bukan dokter (pasien) mengakses Konsultasi Masuk.
     * Output: akses ditolak (HTTP 403), tidak ada data berubah.
     */
    public function test_exception_tc15_pasien_membuka_konsultasi_masuk_ditolak_403(): void
    {
        $this->actingAs($this->pasien)->get(route('dokter.konsultasi.index'))->assertStatus(403);
        $this->actingAs($this->pasien)->post(route('dokter.konsultasi.mulai', $this->konsultasi))->assertStatus(403);

        $this->assertEquals('pending', $this->konsultasi->fresh()->status);
    }

    /**
     * TC-16 (Exception): Akun ber-role dokter tetapi belum terdaftar di tabel doctors.
     * Output: akses ditolak (HTTP 403).
     */
    public function test_exception_tc16_akun_dokter_tanpa_data_doctors_ditolak_403(): void
    {
        $dokterBaru = User::forceCreate([
            'name' => 'dr. Rudi Hartono',
            'email' => 'dr.rudi@bonamu.test',
            'no_hp' => '081234567823',
            'role' => 'dokter',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->actingAs($dokterBaru)->get(route('dokter.konsultasi.index'))->assertStatus(403);
    }
}