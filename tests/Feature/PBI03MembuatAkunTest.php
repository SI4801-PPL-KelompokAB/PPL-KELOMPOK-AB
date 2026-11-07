<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PBI03MembuatAkunTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Uji',
            'email' => 'admin.uji@test.com',
            'no_hp' => '08111111110',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_normal_admin_membuat_akun_dokter(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.akun.store'), [
            'name' => 'dr. Contoh Satu',
            'email' => 'dokter.contoh@test.com',
            'no_hp' => '08111111111',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'dokter',
        ]);

        $response->assertRedirect(route('admin.akun.index'));
        $this->assertDatabaseHas('users', ['email' => 'dokter.contoh@test.com', 'role' => 'dokter']);
    }

    public function test_normal_admin_membuat_akun_admin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.akun.store'), [
            'name' => 'Admin Dua',
            'email' => 'admin.dua@test.com',
            'no_hp' => '08111111112',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('admin.akun.index'));
        $this->assertDatabaseHas('users', ['email' => 'admin.dua@test.com', 'role' => 'admin']);
    }

    public function test_exception_email_sudah_dipakai_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.akun.store'), [
            'name' => 'dr. Contoh Satu',
            'email' => 'admin.uji@test.com',
            'no_hp' => '08111111113',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'dokter',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_exception_role_pasien_tidak_boleh_dibuat(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.akun.store'), [
            'name' => 'Pasien Jahat',
            'email' => 'pasien.jahat@test.com',
            'no_hp' => '08111111114',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'pasien',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'pasien.jahat@test.com']);
    }

    public function test_exception_pasien_tidak_boleh_membuat_akun(): void
    {
        $pasien = User::create([
            'name' => 'Pasien Uji',
            'email' => 'pasien.uji@test.com',
            'no_hp' => '08111111115',
            'password' => 'password',
            'role' => 'pasien',
            'is_active' => true,
        ]);

        $this->actingAs($pasien)->get(route('admin.akun.create'))->assertForbidden();
        $this->actingAs($pasien)->get(route('admin.akun.index'))->assertForbidden();
    }

    public function test_exception_guest_tidak_boleh_membuka_halaman_admin(): void
    {
        $this->get(route('admin.akun.create'))->assertForbidden();
        $this->get(route('admin.akun.index'))->assertForbidden();
    }
}
