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

    protected static bool $initialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        User::firstOrCreate(
            ['no_hp' => '081111111111'],
            [
                'name' => 'Admin Bonamu',
                'email' => 'admin.dusk@bonamu.test',
                'role' => 'admin',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        $this->poli = Poli::first();
        $this->doctor = Doctor::first();

        // Hapus jadwal sabtu lama HANYA SEKALI di awal sebelum test 1 jalan.
        // Data dari Skenario 1 tidak akan dihapus saat Skenario 2 jalan dan tetap tersimpan di phpMyAdmin!
        if (! self::$initialized) {
            DoctorSchedule::where('doctor_id', $this->doctor->id)->where('hari', 'sabtu')->delete();
            self::$initialized = true;
        }
    }

    /**
     * Skenario 1 (Normal): Admin membuat jadwal baru pada hari Sabtu (data masuk ke phpMyAdmin).
     */
    public function test_admin_bisa_membuat_jadwal_praktik_baru(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(600)
                ->type('no_hp', '081111111111')
                ->type('password', 'password')
                ->press('Login')
                ->pause(1000)
                ->visit('/admin/jadwal/create')
                ->pause(1000)
                ->select('doctor_id', (string) $this->doctor->id)
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

    /**
     * Skenario 2 (Exception): Admin mencoba memasukkan jadwal bertabrakan / bentrok pada hari yang sama.
     */
    public function test_admin_melihat_peringatan_saat_jadwal_bentrok(): void
    {
        // Pastikan jadwal bentrok ada (jika Skenario 2 dijalankan terpisah tanpa Skenario 1)
        DoctorSchedule::firstOrCreate(
            ['doctor_id' => $this->doctor->id, 'hari' => 'sabtu', 'poli_id' => $this->poli->id],
            [
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 20,
                'status' => true,
            ]
        );

        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/jadwal/create')
                ->pause(1000)
                ->select('doctor_id', (string) $this->doctor->id)
                ->select('poli_id', (string) $this->poli->id)
                ->select('hari', 'sabtu')
                ->type('kuota', '15')
                ->pause(800)
                ->press('Simpan Jadwal')
                ->pause(1500)
                ->assertSee('bertabrakan');
        });
    }
}
