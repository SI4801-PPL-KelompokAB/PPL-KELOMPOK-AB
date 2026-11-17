<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI01_RegisTest extends DuskTestCase
{
    /**
     * Normal Testing
     * TC-01: Registrasi dengan data valid
     */
    public function test_tc01_registrasi_dengan_data_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $unique = time();
            $email = "pasien{$unique}@test.com";
            $noHp = '08' . substr($unique, -10);
            $password = 'password123';

            $browser->visit('/register')
                    ->pause(2000)
                    ->assertSee('Registrasi Pasien Baru')
                    ->type('name', 'Pasien Valid TC01')
                    ->pause(1500)
                    ->type('email', $email)
                    ->pause(1500)
                    ->type('no_hp', $noHp)
                    ->pause(1500)
                    ->type('password', 'password123')
                    ->pause(1500)
                    ->type('password_confirmation', 'password123')
                    ->pause(1500)
                    ->press('Daftar Sekarang')
                    ->waitForLocation('/login')
                    ->assertPathIs('/login')
                    ->assertSee('Registrasi berhasil! Silakan login.')
                    ->pause(2000);

                    // Simpan akun pasien yang baru terdaftar agar bisa dipakai di PBI02_LoginTest
                    file_put_contents(
                storage_path('framework/testing/registered_pasien.json'),
                json_encode([
                    'no_hp' => $noHp,
                    'password' => $password,
                ])
            );
        });
    }

    /**
     * Exception Testing
     * TC-02: Registrasi dengan kolom kosong
     */
    public function test_tc02_registrasi_dengan_kolom_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->pause(2000)
                    ->press('Daftar Sekarang')
                    ->pause(1500)
                    // Browser menahan submit karena atribut HTML5 'required'
                    ->assertPathIs('/register')
                    ->assertSee('Registrasi Pasien Baru')
                    ->pause(1500);
        });
    }

    /**
     * Exception Testing
     * TC-03: Nomor telepon sudah terdaftar
     */
    public function test_tc03_nomor_telepon_sudah_terdaftar(): void
    {
        $this->browse(function (Browser $browser) {
            $unique = time() + 10;
            $existingNoHp = '08' . substr($unique, -10);

            // Siapkan akun dengan nomor telepon yang sudah terdaftar di DB
            User::create([
                'name' => 'Pasien Eksisting',
                'email' => "eksisting{$unique}@test.com",
                'no_hp' => $existingNoHp,
                'password' => 'password123',
                'role' => 'pasien',
                'is_active' => true,
            ]);

            $browser->visit('/register')
                    ->pause(2000)
                    ->type('name', 'Pasien Duplikat HP')
                    ->pause(1500)
                    ->type('email', "baru{$unique}@test.com")
                    ->pause(1500)
                    ->type('no_hp', $existingNoHp)
                    ->pause(1500)
                    ->type('password', 'password123')
                    ->pause(1500)
                    ->type('password_confirmation', 'password123')
                    ->pause(1500)
                    ->press('Daftar Sekarang')
                    ->waitForLocation('/register')
                    ->assertSee('Nomor telepon sudah terdaftar.')
                    ->pause(2000);
        });
    }

    /**
     * Exception Testing
     * TC-04: Format nomor telepon tidak valid
     */
    public function test_tc04_format_nomor_telepon_tidak_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $unique = time() + 20;

            $browser->visit('/register')
                    ->pause(2000)
                    ->type('name', 'Pasien Format HP Salah')
                    ->pause(1500)
                    ->type('email', "format{$unique}@test.com")
                    ->pause(1500)
                    // Menguji input melebihi batas max:20 karakter sesuai aturan di AuthController
                    ->type('no_hp', '0812345678901234567890123')
                    ->pause(1500)
                    ->type('password', 'password123')
                    ->pause(1500)
                    ->type('password_confirmation', 'password123')
                    ->pause(1500)
                    ->press('Daftar Sekarang')
                    ->waitForLocation('/register')
                    ->assertPresent('.alert-danger')
                    ->pause(2000);
        });
    }

    /**
     * Exception Testing
     * TC-05: Password tidak sesuai (konfirmasi password berbeda)
     */
    public function test_tc05_password_tidak_sesuai(): void
    {
        $this->browse(function (Browser $browser) {
            $unique = time() + 30;

            $browser->visit('/register')
                    ->pause(2000)
                    ->type('name', 'Pasien Password Beda')
                    ->pause(1500)
                    ->type('email', "beda{$unique}@test.com")
                    ->pause(1500)
                    ->type('no_hp', '08' . substr($unique, -10))
                    ->pause(1500)
                    ->type('password', 'password123')
                    ->pause(1500)
                    ->type('password_confirmation', 'password999')
                    ->pause(1500)
                    ->press('Daftar Sekarang')
                    ->waitForLocation('/register')
                    ->assertSee('Konfirmasi password tidak cocok.')
                    ->pause(2000);
        });
    }

    /**
     * Exception Testing
     * TC-06: Password tidak memenuhi ketentuan (kurang dari 8 karakter)
     */
    public function test_tc06_password_tidak_memenuhi_ketentuan(): void
    {
        $this->browse(function (Browser $browser) {
            $unique = time() + 40;

            $browser->visit('/register')
                    ->pause(2000)
                    ->type('name', 'Pasien Password Pendek')
                    ->pause(1500)
                    ->type('email', "pendek{$unique}@test.com")
                    ->pause(1500)
                    ->type('no_hp', '08' . substr($unique, -10))
                    ->pause(1500)
                    ->type('password', '12345')
                    ->pause(1500)
                    ->type('password_confirmation', '12345')
                    ->pause(1500)
                    ->press('Daftar Sekarang')
                    ->waitForLocation('/register')
                    ->assertSee('Password minimal 8 karakter.')
                    ->pause(2000);
        });
    }

    /**
     * Exception Testing
     * TC-07: Kegagalan penyimpanan database
     */
    public function test_tc07_kegagalan_penyimpanan_database(): void
    {
        $this->browse(function (Browser $browser) {
            $unique = time() + 50;
            $emailGagal = "dbfail{$unique}@test.com";

            // Simulasi kondisi kegagalan penyimpanan: input karakter emoji 4-byte / karakter khusus
            // atau panjang ekstrem jika kolom DB tidak mendukung, dan memastikan data tidak tersimpan
            $browser->visit('/register')
                    ->pause(2000)
                    ->type('name', str_repeat('A', 260)) // Melebihi batas kolom VARCHAR(255)
                    ->pause(1500)
                    ->type('email', $emailGagal)
                    ->pause(1500)
                    ->type('no_hp', '08' . substr($unique, -10))
                    ->pause(1500)
                    ->type('password', 'password123')
                    ->pause(1500)
                    ->type('password_confirmation', 'password123')
                    ->pause(1500)
                    ->press('Daftar Sekarang')
                    ->pause(1500);

            // Pastikan sistem menolak penyimpanan dan akun tidak masuk ke tabel users
            $this->assertDatabaseMissing('users', [
                'email' => $emailGagal,
            ]);
        });
    }
}