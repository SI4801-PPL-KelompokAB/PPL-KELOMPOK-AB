<?php

namespace Tests\Browser;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\KlinikCabang;
use App\Models\PatientProfile;
use App\Models\Poli;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI11 - Menangani Konsultasi Online (SPK-31)
 * Uji browser untuk TC-02/TC-03/TC-04, TC-06, TC-08, dan TC-12.
 */
class PBI11MenanganiKonsultasiOnlineBrowserTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected User $dokter;

    protected User $dokterLain;

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

        $doctor = Doctor::create([
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

        Doctor::create([
            'user_id' => $this->dokterLain->id,
            'nomor_sip' => 'SIP-UMUM-002',
        ]);

        // Jadwal praktik dr. Andi untuk besok
        $besok = Carbon::tomorrow();
        $schedule = DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'poli_id' => $poli->id,
            'hari' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'][$besok->dayOfWeekIso - 1],
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '13:00:00',
            'kuota' => 10,
            'status' => true,
        ]);

        // Setup Pasien
        $pasien = User::forceCreate([
            'name' => 'Budi Santoso',
            'email' => 'budi@bonamu.test',
            'no_hp' => '081234567822',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $profil = PatientProfile::create([
            'user_id' => $pasien->id,
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Telekomunikasi No. 1',
        ]);

        // Konsultasi berstatus Menunggu (pending) dari Budi untuk dr. Andi
        $this->konsultasi = Consultation::create([
            'nomor_konsultasi' => 'KON-'.$besok->format('Ymd').'-0001',
            'patient_profile_id' => $profil->id,
            'doctor_id' => $doctor->id,
            'doctor_schedule_id' => $schedule->id,
            'tanggal_konsultasi' => $besok->toDateString(),
            'subject' => 'Demam dan batuk sejak 2 hari',
            'keluhan' => 'Demam dan batuk sejak 2 hari',
            'status' => 'pending',
        ]);
    }

    /** URL ruang konsultasi. */
    private function urlRuang(): string
    {
        return '/konsultasi/'.$this->konsultasi->id.'/ruang';
    }

    /**
     * TC-02, TC-03, TC-04 (Normal): Dokter memulai sesi, mengirim pesan, lalu mengakhiri sesi.
     */
    public function test_normal_tc02_tc03_tc04_dokter_memulai_mengirim_pesan_dan_mengakhiri_sesi(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->dokter)
                ->visit('/dokter/konsultasi')
                ->assertSee($this->konsultasi->nomor_konsultasi)
                ->assertSee('Budi Santoso')
                // TC-02: mulai sesi
                ->click('@tombol-mulai-sesi-'.$this->konsultasi->id)
                ->waitForText('Sesi dimulai.')
                ->assertPathIs($this->urlRuang())
                ->assertSeeIn('@status-konsultasi', 'Berlangsung')
                // TC-03: kirim pesan
                ->type('@input-pesan', 'Halo, sudah minum obat?')
                ->click('@tombol-kirim-pesan')
                ->waitForTextIn('@riwayat-chat', 'Halo, sudah minum obat?')
                ->assertSeeIn('@riwayat-chat', 'Anda')
                // TC-04: akhiri sesi
                ->click('@tombol-akhiri-sesi')
                ->waitForText('Sesi diakhiri.')
                ->assertPathIs('/dokter/konsultasi')
                ->assertSeeIn('@tombol-buka-ruang-'.$this->konsultasi->id, 'Lihat');
        });

        $this->assertEquals('completed', $this->konsultasi->fresh()->status);
        $this->assertDatabaseHas('consultation_messages', [
            'consultation_id' => $this->konsultasi->id,
            'sender_user_id' => $this->dokter->id,
            'message' => 'Halo, sudah minum obat?',
        ]);
    }

    /**
     * TC-06 (Exception): Dokter mengirim pesan kosong.
     */
    public function test_exception_tc06_kirim_pesan_kosong(): void
    {
        $this->konsultasi->update(['status' => 'ongoing']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->dokter)
                ->visit($this->urlRuang())
                ->click('@tombol-kirim-pesan')
                ->waitForText('Pesan wajib diisi.')
                ->assertPathIs($this->urlRuang());
        });

        $this->assertDatabaseCount('consultation_messages', 0);
    }

    /**
     * TC-08 (Exception): Sebelum sesi dimulai, form pesan tidak tampil.
     */
    public function test_exception_tc08_form_pesan_tidak_tampil_sebelum_sesi_dimulai(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->dokter)
                ->visit($this->urlRuang())
                ->assertSeeIn('@info-sesi', 'Menunggu dokter memulai sesi.')
                ->assertMissing('@input-pesan');
        });
    }

    /**
     * TC-12 (Exception): Dokter lain membuka ruang konsultasi milik dr. Andi, akses ditolak (403).
     */
    public function test_exception_tc12_dokter_lain_ditolak_403(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->dokterLain)
                ->visit($this->urlRuang())
                ->assertSee('403')
                ->assertDontSee('Budi Santoso');
        });

        $this->assertEquals('pending', $this->konsultasi->fresh()->status);
    }
}