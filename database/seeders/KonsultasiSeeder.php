<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\KlinikCabang;
use App\Models\PatientProfile;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Data uji Konsultasi Online (PBI10, PBI11).
 * Jalankan: php artisan db:seed --class=KonsultasiSeeder
 *
 * Akun uji (login pakai nomor telepon):
 * - Pasien  Budi Santoso : 081299999999 / password
 * - Dokter  dr. Andi     : 081200000001 / password
 * - Dokter  dr. Sari     : 081200000002 / password
 * - Dokter  drg. Budi    : 081200000003 / password
 * - Dokter  dr. Rina     : 081200000004 / password
 */
class KonsultasiSeeder extends Seeder
{
    /** Password semua akun uji di seeder ini. */
    private const PASSWORD = 'password';

    public function run(): void
    {
        // users.role dan users.is_active belum tentu ada di $fillable model User
        Model::unguarded(function () {
            $cabang = KlinikCabang::firstOrCreate(
                ['nama_cabang' => 'Klinik Bonamu Pusat'],
                ['alamat' => 'Jl. Contoh No. 1', 'nomor_telepon' => '0221234567', 'status' => true]
            );

            $polisData = [
                ['nama_poli' => 'Poli Gigi dan Mulut', 'kode_poli' => 'PGT', 'deskripsi' => 'Layanan pemeriksaan dan perawatan gigi'],
                ['nama_poli' => 'Poli Imunisasi', 'kode_poli' => 'IMN', 'deskripsi' => 'Layanan vaksinasi dan imunisasi rutin'],
                ['nama_poli' => 'Poli KIA', 'kode_poli' => 'KIA', 'deskripsi' => 'Kesehatan ibu dan anak'],
                ['nama_poli' => 'Poli Umum', 'kode_poli' => 'PU', 'deskripsi' => 'Pemeriksaan kesehatan umum'],
            ];

            $polis = collect($polisData)
                ->map(fn ($p) => Poli::firstOrCreate(
                    ['klinik_cabang_id' => $cabang->id, 'nama_poli' => $p['nama_poli']],
                    ['kode_poli' => $p['kode_poli'], 'deskripsi' => $p['deskripsi'], 'status' => true]
                ));

            // [nama, email, no_hp, index poli]
            $daftarDokter = [
                ['dr. Andi', 'dokter.andi@test.com', '081200000001', 0],
                ['dr. Sari', 'dokter.sari@test.com', '081200000002', 0],
                ['drg. Budi', 'dokter.budi@test.com', '081200000003', 1],
                ['dr. Rina', 'dokter.rina@test.com', '081200000004', 2],
            ];

            $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

            foreach ($daftarDokter as $i => [$nama, $email, $noHp, $poliIndex]) {
                // updateOrCreate: akun lama ikut diperbarui (no_hp, password, status) saat seeder dijalankan ulang
                $user = $this->akunUji($email, $nama, $noHp, 'dokter');

                $dokter = Doctor::firstOrCreate(
                    ['user_id' => $user->id],
                    ['nomor_sip' => 'SIP-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT)]
                );

                // dr. Andi praktik dari pagi sampai malam, dokter lain shift siang sampai malam
                [$mulai, $selesai] = $i === 0 ? ['07:00:00', '23:00:00'] : ['15:00:00', '23:00:00'];

                foreach ($hari as $h) {
                    // updateOrCreate: jadwal lama di database ikut diperbarui saat seeder dijalankan ulang
                    DoctorSchedule::updateOrCreate(
                        ['doctor_id' => $dokter->id, 'poli_id' => $polis[$poliIndex]->id, 'hari' => $h],
                        ['jam_mulai' => $mulai, 'jam_selesai' => $selesai, 'kuota' => 10, 'status' => true]
                    );
                }
            }

            $pasien = $this->akunUji('pasien@test.com', 'Budi Santoso', '081299999999', 'pasien');

            PatientProfile::firstOrCreate(
                ['user_id' => $pasien->id],
                ['tanggal_lahir' => '2000-01-01', 'jenis_kelamin' => 'L', 'alamat' => 'Bandung']
            );
        });
    }

    /**
     * Buat akun uji, atau perbarui kalau email sudah ada,
     * supaya nomor HP dan password selalu sesuai daftar di atas.
     */
    private function akunUji(string $email, string $nama, string $noHp, string $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $nama,
                'no_hp' => $noHp,
                'password' => bcrypt(self::PASSWORD),
                'role' => $role,
                'is_active' => true,
            ]
        );
    }
}