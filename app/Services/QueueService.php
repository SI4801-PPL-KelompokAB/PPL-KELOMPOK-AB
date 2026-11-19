<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\DoctorSchedule;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QueueService
{
    private const MAP_HARI = [
        1 => 'senin',
        2 => 'selasa',
        3 => 'rabu',
        4 => 'kamis',
        5 => 'jumat',
        6 => 'sabtu',
        7 => 'minggu',
    ];

    /**
     * Membuat antrean online baru untuk pasien.
     *
     * @throws AturanBisnisException
     */
    public function buatAntreanOnline(User $user, array $data): Queue
    {
        // 1. Validasi profil lengkap
        $profil = $user->patientProfile;
        if (! $profil || ! $profil->isLengkap()) {
            throw new AturanBisnisException('Profil identitas Anda belum lengkap. Silakan lengkapi tanggal lahir, jenis kelamin, dan alamat terlebih dahulu.');
        }

        // 2. Validasi tanggal tidak boleh lewat
        $tanggal = Carbon::parse($data['tanggal_antrean'])->startOfDay();
        $hariIni = today()->startOfDay();

        if ($tanggal->lt($hariIni)) {
            throw new AturanBisnisException('Tanggal antrean tidak boleh tanggal yang sudah lewat.');
        }

        // 3. Validasi dokter punya jadwal aktif pada hari tersebut
        $jadwal = DoctorSchedule::with('doctor.user', 'poli')->find($data['doctor_schedule_id']);
        if (! $jadwal || ! $jadwal->status) {
            throw new AturanBisnisException('Jadwal praktik dokter tidak ditemukan atau sedang nonaktif.');
        }

        $namaHariTanggal = self::MAP_HARI[$tanggal->dayOfWeekIso] ?? '';
        if (strtolower($jadwal->hari) !== $namaHariTanggal) {
            $namaDokter = $jadwal->doctor->user->name ?? 'Dokter';
            throw new AturanBisnisException("Dokter {$namaDokter} tidak praktik pada hari ".ucfirst($namaHariTanggal).'.');
        }

        // 4. Validasi tidak punya antrean aktif ganda
        $antreanAktif = Queue::where('patient_profile_id', $profil->id)
            ->whereIn('status', Queue::ACTIVE_STATUSES)
            ->first();

        if ($antreanAktif) {
            throw new AturanBisnisException(
                "Anda sudah memiliki antrean aktif dengan nomor antrean {$antreanAktif->formatted_nomor}. Selesaikan antrean tersebut terlebih dahulu."
            );
        }

        // 5. Penomoran berurutan & validasi kuota di dalam transaksi dengan lockForUpdate
        return DB::transaction(function () use ($profil, $jadwal, $tanggal) {
            $tanggalString = $tanggal->toDateString();

            // Lock baris antrean pada dokter & tanggal tersebut
            $lastNomor = Queue::where('doctor_schedule_id', $jadwal->id)
                ->whereDate('tanggal_antrean', $tanggalString)
                ->lockForUpdate()
                ->max('nomor_antrean') ?? 0;

            $totalTerdaftar = Queue::where('doctor_schedule_id', $jadwal->id)
                ->whereDate('tanggal_antrean', $tanggalString)
                ->count();

            // Cek kuota antrean
            if ($totalTerdaftar >= $jadwal->kuota) {
                throw new AturanBisnisException('Jadwal Penuh. Kuota antrean untuk jadwal tersebut sudah habis.');
            }

            $nomorAntreanBaru = $lastNomor + 1;

            return Queue::create([
                'patient_profile_id' => $profil->id,
                'doctor_schedule_id' => $jadwal->id,
                'tanggal_antrean' => $tanggalString,
                'nomor_antrean' => $nomorAntreanBaru,
                'status' => Queue::STATUS_WAITING,
            ]);
        });
    }

    /**
     * Mendapatkan hari dari tanggal (senin - minggu).
     */
    public static function hariDariTanggal(string $tanggal): string
    {
        $dayOfWeekIso = Carbon::parse($tanggal)->dayOfWeekIso;

        return self::MAP_HARI[$dayOfWeekIso] ?? 'senin';
    }
}
