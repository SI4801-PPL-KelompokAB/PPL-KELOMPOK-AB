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
 * PBI10 - Melakukan Konsultasi Online dengan Dokter (SPK-32)
 * Uji browser untuk TC-01, TC-04, TC-08, dan TC-10.
 */
class PBI10KonsultasiOnlineBrowserTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected User $pasien;

    protected PatientProfile $profil;

    protected User $dokterUser;

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
            'nama_poli' => 'Poli Umum',
            'status' => true,
        ]);

        // Setup Dokter
        $this->dokterUser = User::forceCreate([
            'name' => 'dr. Andi Pratama',
            'email' => 'dr.andi@bonamu.test',
            'no_hp' => '081234567810',
            'role' => 'dokter',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'user_id' => $this->dokterUser->id,
            'nomor_sip' => 'SIP-UMUM-001',
        ]);

        // Jadwal praktik dokter untuk besok
        $besok = Carbon::tomorrow();
        $this->tanggalValid = $besok->toDateString();

        $this->schedule = DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'][$besok->dayOfWeekIso - 1],
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '13:00:00',
            'kuota' => 5,
            'status' => true,
        ]);

        // Setup Pasien
        $this->pasien = User::forceCreate([
            'name' => 'Budi Santoso',
            'email' => 'budi@bonamu.test',
            'no_hp' => '081234567811',
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
    }

    /** URL form konsultasi untuk poli dan tanggal besok. */
    private function urlForm(): string
    {
        return '/konsultasi/buat?poli='.$this->poli->id.'&tanggal='.$this->tanggalValid;
    }

    /**
     * TC-01 (Normal): Pasien membuat konsultasi dengan data lengkap dan valid, jadwal dokter masih punya kuota.
     */
    public function test_normal_tc01_pasien_membuat_konsultasi_dengan_data_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->pasien)
                ->visit($this->urlForm())
                ->assertSee('dr. Andi Pratama')
                ->assertSee('Sisa kuota: 5')
                ->type('@input-keluhan', 'Demam dan batuk sejak 2 hari')
                ->click('@tombol-buat-konsultasi')
                ->waitForText('Konsultasi berhasil dibuat')
                ->assertSeeIn('@nomor-konsultasi', 'KON-'.Carbon::tomorrow()->format('Ymd').'-0001')
                ->assertSeeIn('@status-konsultasi', 'Menunggu');
        });

        $this->assertDatabaseHas('consultations', [
            'patient_profile_id' => $this->profil->id,
            'status' => 'pending',
        ]);
    }

    /**
     * TC-04 (Exception): Keluhan kurang dari 10 karakter ("Sakit").
     */
    public function test_exception_tc04_keluhan_kurang_dari_10_karakter(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->pasien)
                ->visit($this->urlForm())
                ->type('@input-keluhan', 'Sakit')
                ->click('@tombol-buat-konsultasi')
                ->waitForText('Keluhan minimal 10 karakter')
                ->assertPathIs('/konsultasi/buat');
        });

        $this->assertDatabaseMissing('consultations', [
            'patient_profile_id' => $this->profil->id,
        ]);
    }

    /**
     * TC-08 (Exception): Membuat konsultasi lagi pada jadwal dan tanggal yang sama dengan konsultasi aktifnya.
     */
    public function test_exception_tc08_konsultasi_ganda_pada_jadwal_yang_sama(): void
    {
        Consultation::create([
            'nomor_konsultasi' => 'KON-'.Carbon::tomorrow()->format('Ymd').'-0001',
            'patient_profile_id' => $this->profil->id,
            'doctor_id' => $this->schedule->doctor_id,
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_konsultasi' => $this->tanggalValid,
            'subject' => 'Demam dan batuk sejak 2 hari',
            'keluhan' => 'Demam dan batuk sejak 2 hari',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->pasien)
                ->visit($this->urlForm())
                ->type('@input-keluhan', 'Batuk berdahak dan pilek')
                ->click('@tombol-buat-konsultasi')
                ->waitForText('Anda sudah memiliki konsultasi pada jadwal ini')
                ->assertPathIs('/konsultasi/buat');
        });

        $this->assertEquals(1, Consultation::where('patient_profile_id', $this->profil->id)->count());
    }

    /**
     * TC-10 (Exception): Pengguna bukan pasien (dokter) mengakses halaman buat konsultasi, akses ditolak (403).
     */
    public function test_exception_tc10_bukan_pasien_ditolak_403(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->dokterUser)
                ->visit($this->urlForm())
                ->assertSee('403')
                ->assertMissing('@tombol-buat-konsultasi');
        });

        $this->assertDatabaseCount('consultations', 0);
    }
}