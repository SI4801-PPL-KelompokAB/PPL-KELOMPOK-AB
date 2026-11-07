<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PBI04ProfilPasienTest extends TestCase
{
    use RefreshDatabase;

    private User $pasien;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pasien = User::create([
            'name' => 'Pasien Uji',
            'email' => 'pasien.uji@test.com',
            'no_hp' => '08111111120',
            'password' => 'password',
            'role' => 'pasien',
            'is_active' => true,
        ]);
        $this->pasien->patientProfile()->create([]);
    }

    public function test_normal_pasien_mengisi_profil_bpjs(): void
    {
        $response = $this->actingAs($this->pasien)->put(route('pasien.profil.update'), [
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Contoh No. 1, Bandung',
            'jenis_pasien' => 'BPJS',
            'no_bpjs' => '0001234567890',
        ]);

        $response->assertRedirect(route('pasien.profil.edit'));
        $this->assertDatabaseHas('patient_profiles', [
            'user_id' => $this->pasien->id,
            'jenis_pasien' => 'BPJS',
            'no_bpjs' => '0001234567890',
        ]);
        $this->assertTrue($this->pasien->patientProfile->isLengkap());
    }

    public function test_normal_pasien_mengisi_profil_umum_tanpa_bpjs(): void
    {
        $response = $this->actingAs($this->pasien)->put(route('pasien.profil.update'), [
            'tanggal_lahir' => '1995-06-15',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Umum No. 2, Bandung',
            'jenis_pasien' => 'UMUM',
        ]);

        $response->assertRedirect(route('pasien.profil.edit'));
        $this->assertTrue($this->pasien->patientProfile->isLengkap());
    }

    public function test_exception_bpjs_tanpa_nomor_ditolak(): void
    {
        $response = $this->actingAs($this->pasien)->put(route('pasien.profil.update'), [
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Contoh No. 1',
            'jenis_pasien' => 'BPJS',
        ]);

        $response->assertSessionHasErrors('no_bpjs');
    }

    public function test_exception_umum_dengan_nomor_bpjs_ditolak(): void
    {
        $response = $this->actingAs($this->pasien)->put(route('pasien.profil.update'), [
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Contoh No. 1',
            'jenis_pasien' => 'UMUM',
            'no_bpjs' => '0001234567890',
        ]);

        $response->assertSessionHasErrors('no_bpjs');
    }

    public function test_exception_dokter_tidak_boleh_mengisi_profil_pasien(): void
    {
        $dokter = User::create([
            'name' => 'dr. Uji',
            'email' => 'dokter.uji@test.com',
            'no_hp' => '08111111121',
            'password' => 'password',
            'role' => 'dokter',
            'is_active' => true,
        ]);

        $this->actingAs($dokter)->get(route('pasien.profil.edit'))->assertForbidden();
    }
}
