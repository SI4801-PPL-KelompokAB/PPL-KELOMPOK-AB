<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\DoctorSchedule;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// =========================================================================
// service logika bisnis antrean online:
// - pbi 09 (spk-4): pembuatan antrean online baru (sprint 1)
// - pbi 16 (spk-44): pembatalan & reschedule antrean oleh pasien (sprint 2)
// =========================================================================
class QueueService
{
    // daftar urutan angka hari iso (1 = senin sampai 7 = minggu)
    private const MAP_HARI = [
        1 => 'senin',
        2 => 'selasa',
        3 => 'rabu',
        4 => 'kamis',
        5 => 'jumat',
        6 => 'sabtu',
        7 => 'minggu',
    ];

    // =====================================================================
    // [pbi 09 - spk-4] proses utama pembuatan nomor antrean online pasien
    // =====================================================================
    public function buatAntreanOnline(User $user, array $data): Queue
    {
        // 1. cek apakah profil identitas pasien sudah lengkap (tgl lahir, jenis kelamin, alamat)
        $profil = $user->patientProfile;
        if (! $profil || ! $profil->isLengkap()) {
            throw new AturanBisnisException('Profil identitas Anda belum lengkap. Silakan lengkapi tanggal lahir, jenis kelamin, dan alamat terlebih dahulu.');
        }

        // 2. cek tanggal kunjungan tidak boleh tanggal yang sudah lewat (masa lalu)
        $tanggal = Carbon::parse($data['tanggal_antrean'])->startOfDay();
        $hariIni = today()->startOfDay();

        if ($tanggal->lt($hariIni)) {
            throw new AturanBisnisException('Tanggal antrean tidak boleh tanggal yang sudah lewat.');
        }

        // 3. cek apakah jadwal dokter ada, sedang aktif, dan harinya sesuai dengan tanggal yang dipilih
        $jadwal = DoctorSchedule::with('doctor.user', 'poli')->find($data['doctor_schedule_id']);
        if (! $jadwal || ! $jadwal->status) {
            throw new AturanBisnisException('Jadwal praktik dokter tidak ditemukan atau sedang nonaktif.');
        }

        $namaHariTanggal = self::MAP_HARI[$tanggal->dayOfWeekIso] ?? '';
        if (strtolower($jadwal->hari) !== $namaHariTanggal) {
            $namaDokter = $jadwal->doctor->user->name ?? 'Dokter';
            throw new AturanBisnisException("Dokter {$namaDokter} tidak praktik pada hari ".ucfirst($namaHariTanggal).'.');
        }

        // 4. cek apakah pasien masih punya antrean aktif (mencegah pasien ambil antrean ganda)
        $antreanAktif = Queue::where('patient_profile_id', $profil->id)
            ->whereIn('status', Queue::ACTIVE_STATUSES)
            ->first();

        if ($antreanAktif) {
            throw new AturanBisnisException(
                "Anda sudah memiliki antrean aktif dengan nomor antrean {$antreanAktif->formatted_nomor}. Selesaikan antrean tersebut terlebih dahulu."
            );
        }

        // 5. hitung nomor urut antrean & cek sisa kuota di dalam transaksi database
        return DB::transaction(function () use ($profil, $jadwal, $tanggal) {
            $tanggalString = $tanggal->toDateString();

            // kunci baris antrean (lockforupdate) supaya kalau ada 2 pasien klik bersamaan, nomornya tidak kembar
            $lastNomor = Queue::where('doctor_schedule_id', $jadwal->id)
                ->whereDate('tanggal_antrean', $tanggalString)
                ->lockForUpdate()
                ->max('nomor_antrean') ?? 0;

            // hitung kuota terpakai (antrean yang sudah dibatalkan/skipped tidak mengurangi kuota)
            $totalTerdaftar = Queue::where('doctor_schedule_id', $jadwal->id)
                ->whereDate('tanggal_antrean', $tanggalString)
                ->where('status', '!=', Queue::STATUS_SKIPPED)
                ->count();

            // tolak kalau jumlah antrean sudah mencapai batas kuota jadwal dokter
            if ($totalTerdaftar >= $jadwal->kuota) {
                throw new AturanBisnisException('Jadwal Penuh. Kuota antrean untuk jadwal tersebut sudah habis.');
            }

            // nomor antrean baru = nomor terakhir ditambah 1
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

    // =====================================================================
    // [pbi 16 - spk-44] proses pembatalan antrean online oleh pasien
    // =====================================================================
    public function batalkanAntrean(Queue $queue): Queue
    {
        // hanya antrean yang masih berstatus menunggu (waiting) yang boleh dibatalkan
        if ($queue->status !== Queue::STATUS_WAITING) {
            throw new AturanBisnisException('Hanya antrean yang masih berstatus Menunggu yang dapat dibatalkan.');
        }

        // ubah status menjadi skipped (dibatalkan/dilewati) agar kuota jadwal kembali tersedia
        $queue->update([
            'status' => Queue::STATUS_SKIPPED,
        ]);

        return $queue;
    }

    // =====================================================================
    // [pbi 16 - spk-44] proses penjadwalan ulang (reschedule) antrean pasien
    // =====================================================================
    public function rescheduleAntrean(Queue $queue, array $data): Queue
    {
        // 1. pastikan antrean masih berstatus menunggu (waiting)
        if ($queue->status !== Queue::STATUS_WAITING) {
            throw new AturanBisnisException('Hanya antrean yang masih berstatus Menunggu yang dapat dijadwalkan ulang.');
        }

        // 2. cek tanggal kunjungan baru tidak boleh tanggal masa lalu
        $tanggalBaru = Carbon::parse($data['tanggal_antrean'])->startOfDay();
        $hariIni = today()->startOfDay();

        if ($tanggalBaru->lt($hariIni)) {
            throw new AturanBisnisException('Tanggal antrean baru tidak boleh tanggal yang sudah lewat.');
        }

        $tanggalString = $tanggalBaru->toDateString();

        // 3. tolak jika pasien memilih jadwal dan tanggal yang sama persis dengan antrean saat ini
        if (
            (int) $queue->doctor_schedule_id === (int) $data['doctor_schedule_id']
            && $queue->tanggal_antrean->toDateString() === $tanggalString
        ) {
            throw new AturanBisnisException('Anda sudah terdaftar pada jadwal dan tanggal tersebut.');
        }

        // 4. cek jadwal dokter tujuan harus aktif dan sesuai hari dari tanggal baru
        $jadwalBaru = DoctorSchedule::with('doctor.user', 'poli')->find($data['doctor_schedule_id']);
        if (! $jadwalBaru || ! $jadwalBaru->status) {
            throw new AturanBisnisException('Jadwal praktik dokter tujuan tidak ditemukan atau sedang nonaktif.');
        }

        $namaHariBaru = self::MAP_HARI[$tanggalBaru->dayOfWeekIso] ?? '';
        if (strtolower($jadwalBaru->hari) !== $namaHariBaru) {
            $namaDokter = $jadwalBaru->doctor->user->name ?? 'Dokter';
            throw new AturanBisnisException("Dokter {$namaDokter} tidak praktik pada hari ".ucfirst($namaHariBaru).'.');
        }

        // 5. hitung nomor antrean baru dan cek kuota jadwal tujuan di dalam transaksi database
        return DB::transaction(function () use ($queue, $jadwalBaru, $tanggalString) {
            $lastNomor = Queue::where('doctor_schedule_id', $jadwalBaru->id)
                ->whereDate('tanggal_antrean', $tanggalString)
                ->lockForUpdate()
                ->max('nomor_antrean') ?? 0;

            $totalTerdaftar = Queue::where('doctor_schedule_id', $jadwalBaru->id)
                ->whereDate('tanggal_antrean', $tanggalString)
                ->where('status', '!=', Queue::STATUS_SKIPPED)
                ->where('id', '!=', $queue->id)
                ->count();

            if ($totalTerdaftar >= $jadwalBaru->kuota) {
                throw new AturanBisnisException('Jadwal Penuh. Kuota antrean untuk jadwal tersebut sudah habis.');
            }

            $queue->update([
                'doctor_schedule_id' => $jadwalBaru->id,
                'tanggal_antrean' => $tanggalString,
                'nomor_antrean' => $lastNomor + 1,
                'status' => Queue::STATUS_WAITING,
            ]);

            return $queue->fresh(['doctorSchedule.doctor.user', 'doctorSchedule.poli']);
        });
    }

    // ubah tanggal (misal 2026-11-20) menjadi nama hari bahasa indonesia (senin - minggu)
    public static function hariDariTanggal(string $tanggal): string
    {
        $dayOfWeekIso = Carbon::parse($tanggal)->dayOfWeekIso;

        return self::MAP_HARI[$dayOfWeekIso] ?? 'senin';
    }
}
