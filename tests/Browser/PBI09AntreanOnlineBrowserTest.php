<?php

namespace Tests\Browser;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Poli;
use App\Models\Queue;
use App\Models\User;
use App\Services\QueueService;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI09AntreanOnlineBrowserTest extends DuskTestCase
{
    protected string $tanggalTarget;

    protected DoctorSchedule $schedule;

    protected Poli $poli;

    protected PatientProfile $profil;

    protected User $pasien;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pasien = User::firstOrCreate(
            ['no_hp' => '089876543210'],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.dusk@bonamu.test',
                'role' => 'pasien',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        $this->profil = PatientProfile::updateOrCreate(
            ['user_id' => $this->pasien->id],
            [
                'tanggal_lahir' => '1995-05-15',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Bojongsoang Asri No. 10',
            ]
        );

        $this->poli = Poli::withoutGlobalScopes()->first();
        $doctor = Doctor::first();

        $besok = Carbon::tomorrow();
        $this->tanggalTarget = $besok->toDateString();
        $namaHariBesok = QueueService::hariDariTanggal($this->tanggalTarget);

        $this->schedule = DoctorSchedule::updateOrCreate(
            ['doctor_id' => $doctor->id, 'poli_id' => $this->poli->id, 'hari' => $namaHariBesok],
            [
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 10,
                'status' => true,
            ]
        );
    }

    /**
     * Helper: Login otomatis lewat form jika sesi browser belum login sebagai Pasien Budi.
     */
    protected function pastikanLoginPasien(Browser $browser, string $targetUrl): void
    {
        $browser->visit($targetUrl)->pause(600);

        if (str_contains($browser->driver->getCurrentURL(), '/login')) {
            $browser->type('no_hp', '089876543210')
                ->type('password', 'password')
                ->click('button[type="submit"]')
                ->pause(1000)
                ->visit($targetUrl)
                ->pause(800);
        }
    }

    /**
     * TC-PBI09-01 (Normal): Pasien melihat halaman pendaftaran antrean online.
     */
    public function test_tc_pbi09_01_pasien_melihat_halaman_pendaftaran_antrean(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginPasien($browser, '/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget);

            $browser->pause(800)
                ->assertSee('Pendaftaran Antrean Online')
                ->assertSee($this->schedule->doctor->user->name);
        });
    }

    /**
     * TC-PBI09-02 (Normal): Pasien mengambil nomor antrean online pertama (data masuk ke phpMyAdmin).
     */
    public function test_tc_pbi09_02_pasien_berhasil_membuat_antrean_online_pertama(): void
    {
        Queue::where('doctor_schedule_id', $this->schedule->id)
            ->whereDate('tanggal_antrean', $this->tanggalTarget)
            ->delete();
        Queue::where('patient_profile_id', $this->profil->id)->delete();

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginPasien($browser, '/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget);

            $browser->pause(800)
                ->click('@btn-ambil-antrean-'.$this->schedule->id)
                ->pause(500)
                ->acceptDialog()
                ->pause(1500)
                ->assertSee('Nomor Antrean Anda')
                ->assertSee('A-001');
        });
    }

    /**
     * TC-PBI09-03 (Normal): Penomoran antrean berurutan per dokter per tanggal (A-001 lalu A-002).
     */
    public function test_tc_pbi09_03_penomoran_antrean_berurutan_per_dokter_per_tanggal(): void
    {
        Queue::firstOrCreate(
            [
                'patient_profile_id' => $this->profil->id,
                'tanggal_antrean' => $this->tanggalTarget,
                'doctor_schedule_id' => $this->schedule->id,
            ],
            [
                'nomor_antrean' => 1,
                'status' => Queue::STATUS_WAITING,
            ]
        );

        $pasienKedua = User::firstOrCreate(
            ['no_hp' => '089876543299'],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'siti.dusk@bonamu.test',
                'role' => 'pasien',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        $profilKedua = PatientProfile::updateOrCreate(
            ['user_id' => $pasienKedua->id],
            [
                'tanggal_lahir' => '1997-08-20',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Telekomunikasi No. 5',
            ]
        );

        Queue::where('patient_profile_id', $profilKedua->id)->delete();

        $this->browse(function (Browser $browser) use ($pasienKedua) {
            $browser->loginAs($pasienKedua)
                ->visit('/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget)
                ->pause(1000)
                ->click('@btn-ambil-antrean-'.$this->schedule->id)
                ->pause(500)
                ->acceptDialog()
                ->pause(1500)
                ->assertSee('Nomor Antrean Anda')
                ->assertSee('A-002');

            // Kembalikan sesi ke Budi
            $browser->loginAs($this->pasien);
        });
    }

    /**
     * TC-PBI09-04 (Normal): Pasien melihat detail tiket bukti nomor antrean miliknya.
     */
    public function test_tc_pbi09_04_pasien_melihat_detail_tiket_antrean_miliknya(): void
    {
        $queue = Queue::firstOrCreate(
            [
                'patient_profile_id' => $this->profil->id,
                'tanggal_antrean' => $this->tanggalTarget,
                'doctor_schedule_id' => $this->schedule->id,
            ],
            [
                'nomor_antrean' => 1,
                'status' => Queue::STATUS_WAITING,
            ]
        );

        $this->browse(function (Browser $browser) use ($queue) {
            $this->pastikanLoginPasien($browser, '/antrean/'.$queue->id);

            $browser->pause(1000)
                ->assertSee('KLINIK BONA MITRA KELUARGA')
                ->assertSee('Nomor Antrean Anda')
                ->assertSee($queue->formatted_nomor);
        });
    }

    /**
     * TC-PBI09-05 (Exception): Pasien yang data profilnya belum lengkap ditolak mengambil antrean.
     */
    public function test_tc_pbi09_05_ditolak_jika_profil_pasien_belum_lengkap(): void
    {
        $this->profil->update(['alamat' => null]);

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginPasien($browser, '/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget);

            $browser->pause(1000)
                ->assertSee('Identitas Profil Belum Lengkap')
                ->assertSee('Lengkapi Profil Pasien');
        });

        // Kembalikan alamat profil Budi agar tetap lengkap
        $this->profil->update(['alamat' => 'Jl. Bojongsoang Asri No. 10']);
    }

    /**
     * TC-PBI09-06 (Exception): Pasien memilih tanggal kunjungan yang sudah lewat (kemarin).
     */
    public function test_tc_pbi09_06_ditolak_jika_tanggal_antrean_sudah_lewat(): void
    {
        $kemarin = Carbon::yesterday()->toDateString();

        $this->browse(function (Browser $browser) use ($kemarin) {
            $this->pastikanLoginPasien($browser, '/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$kemarin);

            // Sistem otomatis menolak tanggal masa lalu dan mengembalikan ke tanggal hari ini
            $browser->pause(1000)
                ->assertValue('@input-pilih-tanggal', today()->toDateString());
        });
    }

    /**
     * TC-PBI09-07 (Exception): Pasien memilih tanggal di mana dokter tidak memiliki jadwal praktik aktif.
     */
    public function test_tc_pbi09_07_ditolak_jika_dokter_tidak_praktik_pada_tanggal_tersebut(): void
    {
        $this->schedule->update(['status' => false]);

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginPasien($browser, '/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget);

            $browser->pause(1000)
                ->assertSee('Tidak Ada Praktik Dokter');
        });

        // Aktifkan kembali jadwal dokter
        $this->schedule->update(['status' => true]);
    }

    /**
     * TC-PBI09-08 (Exception): Pasien yang sudah memiliki antrean aktif ditolak mengambil antrean baru (ganda).
     */
    public function test_tc_pbi09_08_ditolak_jika_pasien_sudah_memiliki_antrean_aktif(): void
    {
        Queue::firstOrCreate(
            [
                'patient_profile_id' => $this->profil->id,
                'tanggal_antrean' => $this->tanggalTarget,
                'status' => Queue::STATUS_WAITING,
            ],
            [
                'doctor_schedule_id' => $this->schedule->id,
                'nomor_antrean' => 1,
            ]
        );

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginPasien($browser, '/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget);

            $browser->pause(1000)
                ->assertSee('Anda Sudah Memiliki Antrean Aktif')
                ->assertSee('Ada Antrean Aktif');
        });
    }

    /**
     * TC-PBI09-09 (Exception): Pasien mencoba mengambil antrean pada jadwal yang kuotanya sudah penuh.
     */
    public function test_tc_pbi09_09_ditolak_jika_kuota_jadwal_sudah_penuh(): void
    {
        Queue::firstOrCreate(
            [
                'patient_profile_id' => $this->profil->id,
                'tanggal_antrean' => $this->tanggalTarget,
                'doctor_schedule_id' => $this->schedule->id,
            ],
            [
                'nomor_antrean' => 1,
                'status' => Queue::STATUS_WAITING,
            ]
        );

        $this->schedule->update(['kuota' => 1]);

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginPasien($browser, '/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget);

            $browser->pause(1000)
                ->assertSee('Jadwal Penuh');
        });

        // Kembalikan kuota ke 10
        $this->schedule->update(['kuota' => 10]);
    }

    /**
     * TC-PBI09-10 (Exception): Pasien mencoba membuka URL tiket antrean milik pasien lain (403 Forbidden).
     */
    public function test_tc_pbi09_10_pasien_ditolak_melihat_tiket_antrean_orang_lain(): void
    {
        $pasienLain = User::firstOrCreate(
            ['no_hp' => '089876543299'],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'siti.dusk@bonamu.test',
                'role' => 'pasien',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        $profilLain = PatientProfile::updateOrCreate(
            ['user_id' => $pasienLain->id],
            [
                'tanggal_lahir' => '1997-08-20',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Telekomunikasi No. 5',
            ]
        );

        $tiketOrangLain = Queue::firstOrCreate(
            [
                'patient_profile_id' => $profilLain->id,
                'tanggal_antrean' => $this->tanggalTarget,
                'doctor_schedule_id' => $this->schedule->id,
            ],
            [
                'nomor_antrean' => 2,
                'status' => Queue::STATUS_WAITING,
            ]
        );

        $this->browse(function (Browser $browser) use ($tiketOrangLain) {
            $this->pastikanLoginPasien($browser, '/antrean/'.$tiketOrangLain->id);

            $browser->pause(1000)
                ->assertSee('403');
        });
    }
}
