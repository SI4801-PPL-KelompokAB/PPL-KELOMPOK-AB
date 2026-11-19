<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI01_RegisTest extends DuskTestCase
{
    /**
     * Helper untuk mengetik teks karakter demi karakter agar terlihat pelan saat demo.
     */
    protected function ketikPelan(Browser $browser, string $selector, string $teks, int $jedaMs = 100): void
    {
        $browser->clear($selector);

        foreach (str_split($teks) as $karakter) {
            $browser->keys($selector, $karakter)
                    ->pause($jedaMs);
        }
    }

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
                    ->assertSee('Registrasi Pasien Baru');

            $this->ketikPelan($browser, 'input[name="name"]', 'Pasien Valid TC01');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="email"]', $email);
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="no_hp"]', $noHp);
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password"]', $password);
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password_confirmation"]', $password);
            $browser->pause(1200);

            $browser->press('Daftar Sekarang')
                    ->waitForLocation('/login', 10000)
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
                    ->pause(2000);

            $this->ketikPelan($browser, 'input[name="name"]', 'Pasien Duplikat HP');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="email"]', "baru{$unique}@test.com");
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="no_hp"]', $existingNoHp);
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password"]', 'password123');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password_confirmation"]', 'password123');
            $browser->pause(1200);

            $browser->press('Daftar Sekarang')
                    ->waitForLocation('/register', 10000)
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
                    ->pause(2000);

            $this->ketikPelan($browser, 'input[name="name"]', 'Pasien Format HP Salah');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="email"]', "format{$unique}@test.com");
            $browser->pause(800);

            // Menguji input melebihi batas max:20 karakter sesuai aturan di AuthController
            $this->ketikPelan($browser, 'input[name="no_hp"]', '0812345678901234567890123');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password"]', 'password123');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password_confirmation"]', 'password123');
            $browser->pause(1200);

            $browser->press('Daftar Sekarang')
                    ->waitForLocation('/register', 10000)
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
                    ->pause(2000);

            $this->ketikPelan($browser, 'input[name="name"]', 'Pasien Password Beda');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="email"]', "beda{$unique}@test.com");
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="no_hp"]', '08' . substr($unique, -10));
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password"]', 'password123');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password_confirmation"]', 'password999');
            $browser->pause(1200);

            $browser->press('Daftar Sekarang')
                    ->waitForLocation('/register', 10000)
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
                    ->pause(2000);

            $this->ketikPelan($browser, 'input[name="name"]', 'Pasien Password Pendek');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="email"]', "pendek{$unique}@test.com");
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="no_hp"]', '08' . substr($unique, -10));
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password"]', '12345');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password_confirmation"]', '12345');
            $browser->pause(1200);

            $browser->press('Daftar Sekarang')
                    ->waitForLocation('/register', 10000)
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

            $browser->visit('/register')
                    ->pause(2000);

            // Jeda 5ms per huruf khusus input 260 karakter agar tetap terlihat mengalir tanpa terlalu lama
            $this->ketikPelan($browser, 'input[name="name"]', str_repeat('A', 260), 5);
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="email"]', $emailGagal);
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="no_hp"]', '08' . substr($unique, -10));
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password"]', 'password123');
            $browser->pause(800);

            $this->ketikPelan($browser, 'input[name="password_confirmation"]', 'password123');
            $browser->pause(1200);

            $browser->press('Daftar Sekarang')
                    ->pause(1500);

            // Pastikan sistem menolak penyimpanan dan akun tidak masuk ke tabel users
            $this->assertDatabaseMissing('users', [
                'email' => $emailGagal,
            ]);
        });
    }
}