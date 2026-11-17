<?php

namespace Tests\Browser;

use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Queue;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI14_AntreanAdminTest extends DuskTestCase
{
    /**
     * Helper untuk mengambil akun Admin dan data antrean dari database seeder
     * agar setiap test case berjalan mandiri dan bersih.
     */
    protected function siapkanDataUjiAntrean(): array
    {
        // Ambil akun Admin utama dari seeder berdasarkan no_hp atau role admin
        $admin = User::where('no_hp', '081234567890')
            ->orWhere('role', 'admin')
            ->firstOrFail();

        // Ambil profil pasien & jadwal dokter yang sudah ada di database
        $profilPasien = PatientProfile::with('user')->firstOrFail();
        $jadwal = DoctorSchedule::with('poli')->firstOrFail();

        // Hapus data antrean lama milik pasien ini pada hari ini agar nomor urut bersih
        Queue::where('patient_profile_id', $profilPasien->id)
            ->whereDate('tanggal_antrean', today())
            ->delete();

        // Buat 1 data antrean aktif berstatus 'waiting' untuk diuji oleh Admin
        $queue = Queue::create([
            'patient_profile_id' => $profilPasien->id,
            'doctor_schedule_id' => $jadwal->id,
            'tanggal_antrean'    => today()->toDateString(),
            'nomor_antrean'      => 1,
            'status'             => Queue::STATUS_WAITING,
        ]);

        return [$admin, $profilPasien->user, $jadwal->poli, $queue];
    }

    /**
     * Helper khusus untuk melakukan login sebagai role Admin menggunakan no_hp.
     */
    protected function loginSebagaiAdmin(Browser $browser, User $admin): void
    {
        $browser->visit('/login')
            ->pause(2000)
            ->type('no_hp', $admin->no_hp ?? '081234567890')
            ->type('password', 'password123')
            ->press('Masuk')
            ->waitForLocation('/admin/dashboard', 100)
            ->pause(2000);
    }

    /**
     * TC-01: Admin melakukan login dan melihat daftar antrean harian.
     */
    public function test_tc01_admin_melihat_daftar_antrean_harian(): void
    {
        [$admin, $pasienUser, , $queue] = $this->siapkanDataUjiAntrean();

        $this->browse(function (Browser $browser) use ($admin, $pasienUser, $queue) {
            $browser->driver->manage()->deleteAllCookies();

            // 1. Login sebagai Admin
            $this->loginSebagaiAdmin($browser, $admin);

            // 2. Klik menu Antrean di navbar atas dan verifikasi daftar antrean tampil
            $browser->clickLink('Antrean')
                ->waitForLocation('/admin/antrean', 100)
                ->pause(2000)
                ->assertSee('Kelola Antrean Pasien')
                ->assertSee($queue->formatted_nomor)
                ->assertSee($pasienUser->name)
                ->assertSee('Menunggu');
        });
    }

    /**
     * TC-02: Admin melakukan login dan memfilter daftar antrean.
     */
    public function test_tc02_admin_memfilter_daftar_antrean(): void
    {
        [$admin, $pasienUser, $poli, $queue] = $this->siapkanDataUjiAntrean();

        $this->browse(function (Browser $browser) use ($admin, $pasienUser, $poli, $queue) {
            $browser->driver->manage()->deleteAllCookies();

            // 1. Login sebagai Admin
            $this->loginSebagaiAdmin($browser, $admin);

            // 2. Buka halaman antrean dan jalankan filter Poli & Status
            $browser->visit('/admin/antrean')
                ->pause(2000)
                ->select('poli', (string) $poli->id)
                ->select('status', 'waiting')
                ->press('Filter')
                ->pause(2000)
                ->assertSee($queue->formatted_nomor)
                ->assertSee($pasienUser->name);
        });
    }

    /**
     * TC-03: Admin login dan mengubah status antrean dari waiting ke serving.
     */
    public function test_tc03_admin_mengubah_status_waiting_ke_serving(): void
    {
        [$admin, $pasienUser, , $queue] = $this->siapkanDataUjiAntrean();

        $this->browse(function (Browser $browser) use ($admin, $pasienUser, $queue) {
            $browser->driver->manage()->deleteAllCookies();

            // 1. Login sebagai Admin
            $this->loginSebagaiAdmin($browser, $admin);

            // 2. Klik tombol Layani (Serve) pada antrean yang berstatus waiting
            $browser->visit('/admin/antrean')
                ->pause(2000)
                ->assertSee($pasienUser->name)
                ->press('Layani (Serve)')
                ->pause(2000)
                ->assertSee('Sedang Dilayani')
                ->assertSee('berhasil diubah menjadi Sedang Dilayani');

            // 3. Pastikan database ikut terbarui menjadi 'serving'
            $this->assertDatabaseHas('queues', [
                'id'     => $queue->id,
                'status' => Queue::STATUS_SERVING,
            ]);
        });
    }

    /**
     * TC-04: Admin login dan mengubah status antrean dari serving ke completed.
     */
    public function test_tc04_admin_mengubah_status_serving_ke_completed(): void
    {
        [$admin, $pasienUser, , $queue] = $this->siapkanDataUjiAntrean();

        // Kondisi awal: antrean sudah berstatus 'serving'
        $queue->update(['status' => Queue::STATUS_SERVING]);

        $this->browse(function (Browser $browser) use ($admin, $pasienUser, $queue) {
            $browser->driver->manage()->deleteAllCookies();

            // 1. Login sebagai Admin
            $this->loginSebagaiAdmin($browser, $admin);

            // 2. Klik tombol Selesai (Done) pada antrean yang sedang dilayani
            $browser->visit('/admin/antrean')
                ->pause(2000)
                ->assertSee($pasienUser->name)
                ->press('Selesai (Done)')
                ->pause(2000)
                ->assertSee('Selesai')
                ->assertSee('berhasil diubah menjadi Selesai');

            // 3. Pastikan database ikut terbarui menjadi 'completed'
            $this->assertDatabaseHas('queues', [
                'id'     => $queue->id,
                'status' => Queue::STATUS_COMPLETED,
            ]);
        });
    }

    /**
     * TC-05: Admin login dan melihat daftar antrean kosong pada tanggal tertentu.
     */
    public function test_tc05_daftar_antrean_kosong_pada_tanggal_tertentu(): void
    {
        [$admin] = $this->siapkanDataUjiAntrean();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->driver->manage()->deleteAllCookies();

            // 1. Login sebagai Admin
            $this->loginSebagaiAdmin($browser, $admin);

            // 2. Buka halaman antrean untuk tanggal yang kosong (tidak ada pasien daftar)
            $browser->visit('/admin/antrean?tanggal=2027-12-31')
                ->pause(2000)
                ->assertSee('Belum ada data antrean pasien untuk filter ini.');
        });
    }

    /**
     * TC-06 (Exception): Admin login dan pembaruan status dengan nilai tidak valid ditolak.
     */
    public function test_tc06_pembaruan_status_tidak_valid_ditolak(): void
    {
        [$admin, , , $queue] = $this->siapkanDataUjiAntrean();

        $this->browse(function (Browser $browser) use ($admin, $queue) {
            $browser->driver->manage()->deleteAllCookies();

            // 1. Login sebagai Admin
            $this->loginSebagaiAdmin($browser, $admin);

            $browser->visit('/admin/antrean')
                ->pause(2000);

            // 2. Simulasi pengiriman nilai status tidak valid pada form ubah status
            $browser->script("
                const form = document.querySelector('form[action*=\"/admin/antrean/{$queue->id}/status\"]');
                if (form) {
                    form.querySelector('input[name=\"status\"]').value = 'status_ngasal';
                    form.submit();
                }
            ");

            $browser->pause(2000)
                ->assertSee('Terdapat kesalahan pada input:');

            // 3. Pastikan status di database tetap 'waiting' (tidak berubah)
            $this->assertDatabaseHas('queues', [
                'id'     => $queue->id,
                'status' => Queue::STATUS_WAITING,
            ]);
        });
    }
}