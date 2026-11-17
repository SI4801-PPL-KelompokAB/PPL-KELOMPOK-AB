<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI02_LoginTest extends DuskTestCase
{
    /**
     * Mengambil akun pasien hasil registrasi dari PBI01_RegisTest.
     * Fallback ke akun pasien seeder (081299999999 / password) jika PBI01 belum dijalankan.
     */
    protected function getPasienCredentials(): array
    {
        $path = storage_path('framework/testing/registered_pasien.json');

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (!empty($data['no_hp']) && User::where('no_hp', $data['no_hp'])->exists()) {
                return $data;
            }
        }

        // Fallback ke akun pasien dari KonsultasiSeeder
        return [
            'no_hp' => '081299999999',
            'password' => 'password',
        ];
    }

    /**
     * Mengambil kredensial Petugas (Dokter) & Admin sesuai KonsultasiSeeder dan DatabaseSeeder.
     */
    protected function getStaffCredentials(): array
    {
        return [
            'petugas_no_hp' => '081200000001', // dr. Andi (KonsultasiSeeder)
            'petugas_password' => 'password',
            'admin_no_hp' => '081234567890',   // Admin Bonamu (DatabaseSeeder)
            'admin_password' => 'password123',
        ];
    }

    /**
     * Helper untuk membersihkan cookie/sesi login sebelum test berjalan.
     */
    protected function clearSession(Browser $browser): void
    {
        $browser->visit('/login');
        $browser->driver->manage()->deleteAllCookies();
    }

    /**
     * Normal Testing
     * TC-01: Login sebagai Pasien (menggunakan akun hasil registrasi PBI01_RegisTest)
     */
    public function test_tc01_login_sebagai_pasien(): void
    {
        $pasien = $this->getPasienCredentials();

        $this->browse(function (Browser $browser) use ($pasien) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->type('no_hp', $pasien['no_hp'])
                    ->pause(1000)
                    ->type('password', $pasien['password'])
                    ->pause(1000)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/pasien/dashboard')
                    ->assertPathIs('/pasien/dashboard')
                    ->pause(2000);
        });
    }

    /**
     * Normal Testing
     * TC-02: Login sebagai Petugas (dr. Andi dari KonsultasiSeeder)
     */
    public function test_tc02_login_sebagai_petugas(): void
    {
        $staff = $this->getStaffCredentials();

        $this->browse(function (Browser $browser) use ($staff) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->type('no_hp', $staff['petugas_no_hp'])
                    ->pause(1000)
                    ->type('password', $staff['petugas_password'])
                    ->pause(1000)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/dokter/konsultasi')
                    ->assertPathIs('/dokter/konsultasi')
                    ->pause(2000);
        });
    }

    /**
     * Normal Testing
     * TC-03: Login sebagai Admin (Admin Bonamu dari DatabaseSeeder)
     */
    public function test_tc03_login_sebagai_admin(): void
    {
        $staff = $this->getStaffCredentials();

        $this->browse(function (Browser $browser) use ($staff) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->type('no_hp', $staff['admin_no_hp'])
                    ->pause(1000)
                    ->type('password', $staff['admin_password'])
                    ->pause(1000)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard')
                    ->pause(2000);
        });
    }

    /**
     * Exception Testing
     * TC-04: Nomor Telepon Tidak Terdaftar
     */
    public function test_tc04_nomor_telepon_tidak_terdaftar(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->type('no_hp', '089999999999')
                    ->pause(1000)
                    ->type('password', 'password123')
                    ->pause(1000)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/login')
                    ->assertSee('Nomor telepon atau password salah.')
                    ->pause(2000);
        });
    }

    /**
     * Exception Testing
     * TC-05: Password Salah
     */
    public function test_tc05_password_salah(): void
    {
        $pasien = $this->getPasienCredentials();

        $this->browse(function (Browser $browser) use ($pasien) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->type('no_hp', $pasien['no_hp'])
                    ->pause(1000)
                    ->type('password', 'passwordsalah999')
                    ->pause(1000)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/login')
                    ->assertSee('Nomor telepon atau password salah.')
                    ->pause(2000);
        });
    }

    /**
     * Exception Testing
     * TC-06: Nomor Telepon Kosong
     */
    public function test_tc06_nomor_telepon_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->type('password', 'password123')
                    ->pause(1000)
                    ->click('button[type="submit"]')
                    ->pause(1500)
                    ->assertPathIs('/login')
                    ->pause(1500);
        });
    }

    /**
     * Exception Testing
     * TC-07: Password Kosong
     */
    public function test_tc07_password_kosong(): void
    {
        $pasien = $this->getPasienCredentials();

        $this->browse(function (Browser $browser) use ($pasien) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->type('no_hp', $pasien['no_hp'])
                    ->pause(1000)
                    ->click('button[type="submit"]')
                    ->pause(1500)
                    ->assertPathIs('/login')
                    ->pause(1500);
        });
    }

    /**
     * Exception Testing
     * TC-08: Seluruh Input Kosong
     */
    public function test_tc08_seluruh_input_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->click('button[type="submit"]')
                    ->pause(1500)
                    ->assertPathIs('/login')
                    ->pause(1500);
        });
    }

    /**
     * Exception Testing
     * TC-09: Gangguan Koneksi Database
     */
    public function test_tc09_gangguan_koneksi_database(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);

            $browser->visit('/login')
                    ->pause(1500)
                    ->type('no_hp', str_repeat('0', 300))
                    ->pause(1000)
                    ->type('password', 'password123')
                    ->pause(1000)
                    ->click('button[type="submit"]')
                    ->pause(1500)
                    ->visit('/dashboard')
                    ->waitForLocation('/login')
                    ->assertPathIs('/login')
                    ->pause(1500);
        });
    }

    /**
     * Normal Testing
     * TC-10: Pengalihan Dashboard Berdasarkan Role
     */
    public function test_tc10_pengalihan_dashboard_berdasarkan_role(): void
    {
        $pasien = $this->getPasienCredentials();
        $staff = $this->getStaffCredentials();

        $this->browse(function (Browser $browser) use ($pasien, $staff) {
            // 1. Pasien (dari PBI01_RegisTest) -> Dashboard Pasien
            $this->clearSession($browser);
            $browser->visit('/login')
                    ->pause(1000)
                    ->type('no_hp', $pasien['no_hp'])
                    ->pause(800)
                    ->type('password', $pasien['password'])
                    ->pause(800)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/pasien/dashboard')
                    ->assertPathIs('/pasien/dashboard')
                    ->pause(1500);

            // 2. Petugas (dr. Andi) -> Dashboard Petugas
            $this->clearSession($browser);
            $browser->visit('/login')
                    ->pause(1000)
                    ->type('no_hp', $staff['petugas_no_hp'])
                    ->pause(800)
                    ->type('password', $staff['petugas_password'])
                    ->pause(800)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/dokter/konsultasi')
                    ->assertPathIs('/dokter/konsultasi')
                    ->pause(1500);

            // 3. Admin (Admin Bonamu) -> Dashboard Admin
            $this->clearSession($browser);
            $browser->visit('/login')
                    ->pause(1000)
                    ->type('no_hp', $staff['admin_no_hp'])
                    ->pause(800)
                    ->type('password', $staff['admin_password'])
                    ->pause(800)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard')
                    ->pause(1500);
        });
    }
}