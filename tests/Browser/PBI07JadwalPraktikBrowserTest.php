<?php

namespace Tests\Browser;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Poli;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI07JadwalPraktikBrowserTest extends DuskTestCase
{
    protected Doctor $doctor;

    protected Poli $poli;

    protected User $admin;

    protected User $pasien;

    // siapkan akun admin, pasien, poli, dan dokter sebelum skenario test berjalan
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['no_hp' => '081111111111'],
            [
                'name' => 'Admin Bonamu',
                'email' => 'admin.dusk@bonamu.test',
                'role' => 'admin',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

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

        $this->poli = Poli::withoutGlobalScopes()->first();
        $this->doctor = Doctor::first();
    }

    // fungsi bantu: otomatis login sebagai admin di browser kalau belum login
    protected function pastikanLoginAdmin(Browser $browser, string $targetUrl): void
    {
        $browser->visit($targetUrl)->pause(600);

        if (str_contains($browser->driver->getCurrentURL(), '/login')) {
            $browser->type('no_hp', '081111111111')
                ->type('password', 'password')
                ->click('button[type="submit"]')
                ->pause(1000)
                ->visit($targetUrl)
                ->pause(800);
        }
    }

    // tc-pbi07-01 (normal): admin membuka dan melihat halaman daftar jadwal praktik dokter
    public function test_tc_pbi07_01_admin_melihat_daftar_jadwal_praktik(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/jadwal');

            $browser->pause(800)
                ->assertPathIs('/admin/jadwal')
                ->assertSee('Jadwal Praktik Dokter');
        });
    }

    // tc-pbi07-02 (normal): admin membuat jadwal praktik baru di hari sabtu (data masuk ke phpmyadmin)
    public function test_tc_pbi07_02_admin_membuat_jadwal_praktik_baru(): void
    {
        // bersihkan jadwal sabtu lama supaya bisa input jadwal baru dari awal
        DoctorSchedule::where('doctor_id', $this->doctor->id)->where('hari', 'sabtu')->delete();

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/jadwal/create');

            $browser->select('doctor_id', (string) $this->doctor->id)
                ->select('poli_id', (string) $this->poli->id)
                ->select('hari', 'sabtu')
                ->type('kuota', '20')
                ->pause(800)
                ->press('Simpan Jadwal')
                ->pause(1500)
                ->assertPathIs('/admin/jadwal')
                ->assertSee('Jadwal praktik dokter berhasil ditambahkan.');
        });
    }

    // tc-pbi07-03 (normal): admin mengedit kuota jadwal praktik dokter yang sudah ada jadi 25
    public function test_tc_pbi07_03_admin_mengedit_jadwal_praktik(): void
    {
        $jadwal = DoctorSchedule::firstOrCreate(
            ['doctor_id' => $this->doctor->id, 'hari' => 'sabtu', 'poli_id' => $this->poli->id],
            [
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 20,
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) use ($jadwal) {
            $this->pastikanLoginAdmin($browser, '/admin/jadwal/'.$jadwal->id.'/edit');

            $browser->type('@input-kuota', '25')
                ->pause(800)
                ->click('@btn-update-jadwal')
                ->pause(1500)
                ->assertPathIs('/admin/jadwal')
                ->assertSee('Jadwal praktik dokter berhasil diperbarui.');
        });
    }

    // tc-pbi07-04 (normal): admin menonaktifkan status jadwal praktik tanpa menghapus datanya
    public function test_tc_pbi07_04_admin_menonaktifkan_jadwal_praktik(): void
    {
        $jadwal = DoctorSchedule::updateOrCreate(
            ['doctor_id' => $this->doctor->id, 'hari' => 'sabtu', 'poli_id' => $this->poli->id],
            [
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 25,
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) use ($jadwal) {
            $this->pastikanLoginAdmin($browser, '/admin/jadwal');

            $browser->click('@toggle-jadwal-'.$jadwal->id)
                ->pause(500)
                ->acceptDialog()
                ->pause(1500)
                ->assertPathIs('/admin/jadwal')
                ->assertSee('Jadwal praktik berhasil dinonaktifkan.');
        });

        // aktifkan kembali supaya jadwal di phpmyadmin tetap aktif untuk test lain
        $jadwal->update(['status' => true]);
    }

    // tc-pbi07-05 (exception): sistem menolak jadwal kalau jam selesai lebih awal dari jam mulai
    public function test_tc_pbi07_05_ditolak_jika_jam_selesai_lebih_awal_dari_jam_mulai(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/jadwal/create');

            $browser->select('doctor_id', (string) $this->doctor->id)
                ->select('poli_id', (string) $this->poli->id)
                ->select('hari', 'minggu');

            // isi jam mulai 12:00 dan jam selesai 08:00 (terbalik)
            $browser->script([
                "document.querySelector('input[name=\"jam_mulai\"]').value = '12:00';",
                "document.querySelector('input[name=\"jam_selesai\"]').value = '08:00';",
            ]);

            $browser->pause(800)
                ->press('Simpan Jadwal')
                ->pause(1500)
                ->assertSee('Jam selesai harus setelah jam mulai');
        });
    }

    // tc-pbi07-06 (exception): sistem menolak jadwal kalau kuota diisi kurang dari 1 (0)
    public function test_tc_pbi07_06_ditolak_jika_kuota_kurang_dari_satu(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/jadwal/create');

            $browser->select('doctor_id', (string) $this->doctor->id)
                ->select('poli_id', (string) $this->poli->id)
                ->select('hari', 'minggu');

            // lepas batas html min=1 supaya form bisa disubmit dengan angka 0 ke backend
            $browser->script([
                "document.querySelector('input[name=\"kuota\"]').removeAttribute('min');",
            ]);

            $browser->type('kuota', '0')
                ->pause(800)
                ->press('Simpan Jadwal')
                ->pause(1500)
                ->assertSee('minimal 1');
        });
    }

    // tc-pbi07-07 (exception): sistem menolak jadwal kalau jamnya bertabrakan dengan jadwal di hari yang sama
    public function test_tc_pbi07_07_ditolak_jika_jadwal_bentrok(): void
    {
        // pastikan jadwal sabtu jam 08:00-12:00 sudah ada di database
        DoctorSchedule::updateOrCreate(
            ['doctor_id' => $this->doctor->id, 'hari' => 'sabtu', 'poli_id' => $this->poli->id],
            [
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 20,
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) {
            $this->pastikanLoginAdmin($browser, '/admin/jadwal/create');

            // coba input jadwal di dokter, hari, dan jam yang sama
            $browser->select('doctor_id', (string) $this->doctor->id)
                ->select('poli_id', (string) $this->poli->id)
                ->select('hari', 'sabtu')
                ->type('kuota', '15')
                ->pause(800)
                ->press('Simpan Jadwal')
                ->pause(1500)
                ->assertSee('bertabrakan');
        });
    }

    // tc-pbi07-08 (exception): akun non-admin (pasien) ditolak dengan error 403 saat buka halaman jadwal admin
    public function test_tc_pbi07_08_non_admin_ditolak_mengakses_jadwal_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->pasien)
                ->visit('/admin/jadwal')
                ->pause(1000)
                ->assertSee('403');

            // kembalikan login ke admin setelah test selesai
            $browser->loginAs($this->admin);
        });
    }
}
