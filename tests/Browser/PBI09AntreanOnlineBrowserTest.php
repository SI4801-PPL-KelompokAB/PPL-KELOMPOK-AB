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

    protected static bool $initialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        $pasien = User::firstOrCreate(
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
            ['user_id' => $pasien->id],
            [
                'tanggal_lahir' => '1995-05-15',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Bojongsoang Asri No. 10',
            ]
        );

        // Hapus antrean lama Budi HANYA SEKALI di awal sebelum test 1 jalan,
        // sehingga antrean yang diambil di test 1 TIDAK terhapus saat test 2 jalan dan tetap ada di phpMyAdmin!
        if (! self::$initialized) {
            Queue::where('patient_profile_id', $this->profil->id)->delete();
            self::$initialized = true;
        }

        $this->poli = Poli::first();
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
     * Skenario 1 (Normal): Pasien mengambil nomor antrean online (data masuk ke phpMyAdmin).
     */
    public function test_pasien_bisa_mengambil_antrean_online_dan_melihat_tiket(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(600)
                ->type('no_hp', '089876543210')
                ->type('password', 'password')
                ->press('Login')
                ->pause(1000)
                ->visit('/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget)
                ->pause(1500)
                ->assertSee('Pendaftaran Antrean Online')
                ->assertSee($this->schedule->doctor->user->name)
                ->click('@btn-ambil-antrean-'.$this->schedule->id)
                ->pause(500)
                ->acceptDialog()
                ->pause(1500)
                ->assertSee('Nomor Antrean Anda')
                ->assertSee('KLINIK BONA MITRA KELUARGA');
        });
    }

    /**
     * Skenario 2 (Exception): Pasien dilarang mengambil antrean ganda (data antrean aktif tetap ada di phpMyAdmin).
     */
    public function test_pasien_dilarang_mengambil_antrean_ganda_secara_visual(): void
    {
        // Pastikan antrean aktif ada (jika Skenario 2 dijalankan sendiri tanpa Skenario 1)
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
            $browser->visit('/antrean/ambil?poli='.$this->poli->id.'&tanggal='.$this->tanggalTarget)
                ->pause(1200)
                ->assertSee('Anda Sudah Memiliki Antrean Aktif')
                ->assertSee('Ada Antrean Aktif');
        });
    }
}
