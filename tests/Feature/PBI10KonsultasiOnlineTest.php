<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\KlinikCabang;
use App\Models\PatientProfile;
use App\Models\Poli;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI10 - Melakukan Konsultasi Online dengan Dokter (SPK-32)
 * Mengikuti test case scenario TC-01 sampai TC-13.
 */
class PBI10KonsultasiOnlineTest extends TestCase
{
    use RefreshDatabase;

    protected User $pasien;

    protected PatientProfile $profil;

    protected User $pasienLain;

    protected PatientProfile $profilLain;

    protected User $dokterUser;

    protected Doctor $doctor;

    protected Poli $poli;

    protected DoctorSchedule $schedule;

    protected string $tanggalValid;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Cabang & Poli
        $cabang = KlinikCabang::create([
            'nama_cabang' => 'Klinik Bonamu Pusat',
            'alamat' => 'Jl. Sukabirus No. 10',
            'nomor_telepon' => '0229876543',
            'status' => true,
        ]);

        $this->poli = Poli::create([
            'klinik_cabang_id' => $cabang->id,
            'nama_poli' => 'Poli Umum',
            'status' => true,
        ]);

        // Setup Dokter
        $this->dokterUser = User::forceCreate([
            'name' => 'dr. Andi Pratama',
            'email' => 'dr.andi@bonamu.test',
            'no_hp' => '081234567810',
            'role' => 'dokter',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->dokterUser->id,
            'nomor_sip' => 'SIP-UMUM-001',
        ]);

        // Jadwal praktik dokter untuk besok
        $besok = Carbon::tomorrow();
        $this->tanggalValid = $besok->toDateString();

        $this->schedule = DoctorSchedule::create([
            'doctor_id' => $this->doctor->id,
            'poli_id' => $this->poli->id,
            'hari' => $this->namaHari($besok),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '13:00:00',
            'kuota' => 5,
            'status' => true,
        ]);

        // Setup Pasien 1
        $this->pasien = User::forceCreate([
            'name' => 'Budi Santoso',
            'email' => 'budi@bonamu.test',
            'no_hp' => '081234567811',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->profil = PatientProfile::create([
            'user_id' => $this->pasien->id,
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Telekomunikasi No. 1',
        ]);

        // Setup Pasien 2
        $this->pasienLain = User::forceCreate([
            'name' => 'Siti Aminah',
            'email' => 'siti@bonamu.test',
            'no_hp' => '081234567812',
            'role' => 'pasien',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->profilLain = PatientProfile::create([
            'user_id' => $this->pasienLain->id,
            'tanggal_lahir' => '1998-03-12',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Sukapura No. 7',
        ]);
    }

    /** Nama hari sesuai isi kolom doctor_schedules.hari (senin sampai minggu). */
    private function namaHari(Carbon $tanggal): string
    {
        return ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'][$tanggal->dayOfWeekIso - 1];
    }

    /** Data form konsultasi yang valid. */
    private function payload(array $ubah = []): array
    {
        return array_merge([
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_konsultasi' => $this->tanggalValid,
            'keluhan' => 'Demam dan batuk sejak 2 hari',
        ], $ubah);
    }

    /**
     * Keluhan dengan panjang tertentu untuk uji batas 1000 karakter.
     * Karakter terakhir selalu huruf, bukan spasi, agar tidak terpotong oleh trim Laravel.
     */
    private function keluhanPanjang(int $panjang): string
    {
        return substr(str_repeat('Demam, pusing, dan batuk berdahak. ', 40), 0, $panjang);
    }

    /** Konsultasi yang sudah ada pada jadwal dan tanggal besok. */
    private function konsultasiAda(PatientProfile $profil, int $urut): Consultation
    {
        return Consultation::create([
            'nomor_konsultasi' => 'KON-'.Carbon::tomorrow()->format('Ymd').'-'.str_pad($urut, 4, '0', STR_PAD_LEFT),
            'patient_profile_id' => $profil->id,
            'doctor_id' => $this->doctor->id,
            'doctor_schedule_id' => $this->schedule->id,
            'tanggal_konsultasi' => $this->tanggalValid,
            'subject' => 'Demam dan batuk sejak 2 hari',
            'keluhan' => 'Demam dan batuk sejak 2 hari',
            'status' => 'pending',
        ]);
    }

    /** URL form konsultasi untuk poli dan tanggal besok. */
    private function urlForm(): string
    {
        return route('konsultasi.create', ['poli' => $this->poli->id, 'tanggal' => $this->tanggalValid]);
    }

    /**
     * TC-01 (Normal): Pasien membuat konsultasi dengan data lengkap dan valid, jadwal dokter masih punya kuota.
     * Output: tersimpan berstatus "menunggu", nomor KON-YYYYMMDD-XXXX, sisa kuota berkurang 1, halaman konfirmasi tampil.
     */
    public function test_normal_tc01_pasien_membuat_konsultasi_dengan_data_valid(): void
    {
        $response = $this->actingAs($this->pasien)->post(route('konsultasi.store'), $this->payload());

        $konsultasi = Consultation::where('patient_profile_id', $this->profil->id)->first();
        $this->assertNotNull($konsultasi);
        $this->assertEquals('pending', $konsultasi->status);
        $this->assertEquals('Menunggu', $konsultasi->status_label);
        $this->assertEquals('KON-'.Carbon::tomorrow()->format('Ymd').'-0001', $konsultasi->nomor_konsultasi);
        $response->assertRedirect(route('konsultasi.show', $konsultasi));

        // Sisa kuota berkurang 1 (5 menjadi 4)
        $this->actingAs($this->pasienLain)->get($this->urlForm())->assertSee('Sisa kuota: 4');

        // Halaman konfirmasi tampil
        $this->actingAs($this->pasien)->get(route('konsultasi.show', $konsultasi))
            ->assertStatus(200)
            ->assertSee($konsultasi->nomor_konsultasi)
            ->assertSee('Menunggu');
    }

    /**
     * TC-02 (Normal): Keluhan tepat pada batas minimal (10 karakter) dan maksimal (1000 karakter).
     * Output: kedua konsultasi diterima dan tersimpan.
     */
    public function test_normal_tc02_keluhan_tepat_batas_minimal_dan_maksimal_diterima(): void
    {
        $keluhan10 = 'Batuk lama'; // 10 karakter
        $keluhan1000 = $this->keluhanPanjang(1000);

        $this->assertEquals(10, strlen($keluhan10));
        $this->assertEquals(1000, strlen($keluhan1000));

        $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload(['keluhan' => $keluhan10]))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->pasienLain)
            ->post(route('konsultasi.store'), $this->payload(['keluhan' => $keluhan1000]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('consultations', ['patient_profile_id' => $this->profil->id, 'keluhan' => $keluhan10]);
        $this->assertDatabaseHas('consultations', ['patient_profile_id' => $this->profilLain->id, 'keluhan' => $keluhan1000]);
    }

    /**
     * TC-03 (Exception): Pasien menekan Buat Konsultasi tanpa mengisi keluhan.
     * Output: data tidak tersimpan, tetap di form, pesan "Keluhan wajib diisi".
     */
    public function test_exception_tc03_keluhan_kosong_ditolak(): void
    {
        $response = $this->actingAs($this->pasien)
            ->from($this->urlForm())
            ->post(route('konsultasi.store'), $this->payload(['keluhan' => '']));

        $response->assertRedirect($this->urlForm());
        $response->assertSessionHasErrors(['keluhan' => 'Keluhan wajib diisi']);
        $this->assertDatabaseCount('consultations', 0);
    }

    /**
     * TC-04 (Exception): Keluhan kurang dari 10 karakter ("Sakit").
     * Output: data tidak tersimpan, pesan "Keluhan minimal 10 karakter".
     */
    public function test_exception_tc04_keluhan_kurang_dari_10_karakter_ditolak(): void
    {
        $response = $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload(['keluhan' => 'Sakit']));

        $response->assertSessionHasErrors(['keluhan' => 'Keluhan minimal 10 karakter']);
        $this->assertDatabaseCount('consultations', 0);
    }

    /**
     * TC-05 (Exception): Keluhan lebih dari 1000 karakter (1001 karakter).
     * Output: data tidak tersimpan, pesan "Keluhan maksimal 1000 karakter".
     */
    public function test_exception_tc05_keluhan_lebih_dari_1000_karakter_ditolak(): void
    {
        $keluhan1001 = $this->keluhanPanjang(1001);
        $this->assertEquals(1001, strlen($keluhan1001));

        $response = $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload(['keluhan' => $keluhan1001]));

        $response->assertSessionHasErrors(['keluhan' => 'Keluhan maksimal 1000 karakter']);
        $this->assertDatabaseCount('consultations', 0);
    }

    /**
     * TC-06 (Exception): Membuat konsultasi pada jadwal dokter yang kuotanya sudah penuh.
     * Output: data tidak tersimpan, kembali ke pilih jadwal, pesan "Kuota jadwal sudah penuh".
     */
    public function test_exception_tc06_jadwal_kuota_penuh_ditolak(): void
    {
        $this->schedule->update(['kuota' => 1]);
        $this->konsultasiAda($this->profilLain, 1);

        $response = $this->actingAs($this->pasien)
            ->from($this->urlForm())
            ->post(route('konsultasi.store'), $this->payload());

        $response->assertRedirect($this->urlForm());
        $response->assertSessionHasErrors(['doctor_schedule_id' => 'Kuota jadwal sudah penuh']);
        $this->assertDatabaseMissing('consultations', ['patient_profile_id' => $this->profil->id]);
    }

    /**
     * TC-07 (Exception): Membuat konsultasi dengan tanggal yang sudah lewat (kemarin).
     * Output: data tidak tersimpan, pesan "Tanggal konsultasi tidak valid".
     */
    public function test_exception_tc07_tanggal_sudah_lewat_ditolak(): void
    {
        $response = $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload([
                'tanggal_konsultasi' => Carbon::yesterday()->toDateString(),
            ]));

        $response->assertSessionHasErrors(['tanggal_konsultasi' => 'Tanggal konsultasi tidak valid']);
        $this->assertDatabaseCount('consultations', 0);
    }

    /**
     * TC-08 (Exception): Membuat konsultasi lagi pada jadwal dan tanggal yang sama dengan konsultasi aktifnya.
     * Output: data tidak tersimpan, pesan "Anda sudah memiliki konsultasi pada jadwal ini".
     */
    public function test_exception_tc08_konsultasi_ganda_pada_jadwal_yang_sama_ditolak(): void
    {
        $this->konsultasiAda($this->profil, 1);

        $response = $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload(['keluhan' => 'Batuk berdahak dan pilek']));

        $response->assertSessionHasErrors(['doctor_schedule_id' => 'Anda sudah memiliki konsultasi pada jadwal ini']);
        $this->assertEquals(1, Consultation::where('patient_profile_id', $this->profil->id)->count());
    }

    /**
     * TC-09 (Exception): Pengguna yang belum login mengakses halaman Konsultasi Online.
     * Output: sistem mengalihkan (redirect) ke halaman login.
     */
    public function test_exception_tc09_pengguna_belum_login_dialihkan_ke_login(): void
    {
        $response = $this->get($this->urlForm());

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * TC-10 (Exception): Pengguna bukan pasien (admin/dokter) mengakses halaman buat konsultasi.
     * Catatan: sesuai standar tim hanya ada role pasien, dokter, admin. Tugas petugas dikerjakan admin.
     * Output: akses ditolak (HTTP 403), tidak ada data tersimpan.
     */
    public function test_exception_tc10_bukan_pasien_ditolak_403(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin Klinik',
            'email' => 'admin@bonamu.test',
            'no_hp' => '081234567813',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get($this->urlForm())->assertStatus(403);
        $this->actingAs($this->dokterUser)->get($this->urlForm())->assertStatus(403);
        $this->actingAs($this->dokterUser)->post(route('konsultasi.store'), $this->payload())->assertStatus(403);

        $this->assertDatabaseCount('consultations', 0);
    }

    /**
     * TC-11 (Exception): Tanggal tidak sesuai hari praktik jadwal.
     * Output: data tidak tersimpan, pesan "Tanggal tidak sesuai jadwal dokter".
     */
    public function test_exception_tc11_tanggal_tidak_sesuai_hari_praktik_ditolak(): void
    {
        // Cari tanggal yang harinya berbeda dengan hari praktik jadwal
        $tanggalLain = Carbon::tomorrow()->addDay();
        while ($this->namaHari($tanggalLain) === $this->schedule->hari) {
            $tanggalLain->addDay();
        }

        $response = $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload([
                'tanggal_konsultasi' => $tanggalLain->toDateString(),
            ]));

        $response->assertSessionHasErrors(['doctor_schedule_id' => 'Tanggal tidak sesuai jadwal dokter']);
        $this->assertDatabaseCount('consultations', 0);
    }

    /**
     * TC-12 (Normal): Kuota jadwal tinggal 1.
     * Output: konsultasi tersimpan, sisa kuota menjadi 0, jadwal tampil penuh.
     */
    public function test_normal_tc12_kuota_tinggal_1_tersimpan_lalu_jadwal_penuh(): void
    {
        $this->schedule->update(['kuota' => 1]);

        $this->actingAs($this->pasien)->get($this->urlForm())->assertSee('Sisa kuota: 1');

        $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('consultations', ['patient_profile_id' => $this->profil->id]);

        // Pasien lain melihat jadwal sudah penuh
        $this->actingAs($this->pasienLain)->get($this->urlForm())
            ->assertSee('Penuh')
            ->assertDontSee('Sisa kuota:');
    }

    /**
     * TC-13 (Exception): Poli atau dokter nonaktif.
     * Output: tidak muncul pada pilihan, dan jika dipaksa lewat request, data ditolak.
     */
    public function test_exception_tc13_poli_atau_dokter_nonaktif_tidak_muncul_dan_ditolak(): void
    {
        // Poli dinonaktifkan Admin
        $this->poli->update(['status' => false]);

        $this->actingAs($this->pasien)->get($this->urlForm())->assertDontSee('dr. Andi Pratama');
        $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload())
            ->assertSessionHasErrors('doctor_schedule_id');

        // Poli aktif lagi, dokter dinonaktifkan Admin
        $this->poli->update(['status' => true]);
        $this->dokterUser->forceFill(['is_active' => false])->save();

        $this->actingAs($this->pasien)->get($this->urlForm())->assertDontSee('dr. Andi Pratama');
        $this->actingAs($this->pasien)
            ->post(route('konsultasi.store'), $this->payload())
            ->assertSessionHasErrors('doctor_schedule_id');

        $this->assertDatabaseCount('consultations', 0);
    }
}