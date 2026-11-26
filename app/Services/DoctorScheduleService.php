<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\DoctorSchedule;
use Carbon\Carbon;

// =========================================================================
// pbi 07 (spk-36): service logika bisnis kelola jadwal praktik dokter (admin)
// =========================================================================
class DoctorScheduleService
{
    // [pbi 07] daftar pilihan hari praktik dokter yang valid
    public const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

    // [pbi 07] buat jadwal praktik baru setelah lolos pengecekan aturan bisnis
    public function buatJadwal(array $data): DoctorSchedule
    {
        $this->validasiAturanJadwal($data);

        return DoctorSchedule::create([
            'doctor_id' => $data['doctor_id'],
            'poli_id' => $data['poli_id'],
            'hari' => strtolower($data['hari']),
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'kuota' => (int) $data['kuota'],
            'status' => $data['status'] ?? true,
        ]);
    }

    // [pbi 07] simpan perubahan edit jadwal setelah lolos pengecekan aturan bisnis
    public function perbaruiJadwal(DoctorSchedule $schedule, array $data): DoctorSchedule
    {
        $this->validasiAturanJadwal($data, $schedule->id);

        $schedule->update([
            'doctor_id' => $data['doctor_id'],
            'poli_id' => $data['poli_id'],
            'hari' => strtolower($data['hari']),
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'kuota' => (int) $data['kuota'],
            'status' => $data['status'] ?? $schedule->status,
        ]);

        return $schedule;
    }

    // [pbi 07] ubah status jadwal (aktif <-> nonaktif) tanpa menghapus data dari database
    public function toggleStatus(DoctorSchedule $schedule): DoctorSchedule
    {
        $statusBaru = ! $schedule->status;

        if ($statusBaru) {
            // kalau jadwal mau diaktifkan kembali, cek dulu jangan sampai jamnya bentrok
            $this->cekJadwalBentrok(
                (int) $schedule->doctor_id,
                $schedule->hari,
                $schedule->jam_mulai,
                $schedule->jam_selesai,
                $schedule->id
            );
        }

        $schedule->update(['status' => $statusBaru]);

        return $schedule;
    }

    // [pbi 07] validasi 3 aturan utama jadwal praktik dokter
    public function validasiAturanJadwal(array $data, ?int $excludeScheduleId = null): void
    {
        $jamMulai = Carbon::parse($data['jam_mulai'])->format('H:i:s');
        $jamSelesai = Carbon::parse($data['jam_selesai'])->format('H:i:s');
        $hari = strtolower($data['hari']);
        $kuota = (int) ($data['kuota'] ?? 0);
        $doctorId = (int) $data['doctor_id'];

        // aturan 1: jam selesai wajib lebih besar dari jam mulai
        if ($jamSelesai <= $jamMulai) {
            throw new AturanBisnisException('Jam selesai harus setelah jam mulai.');
        }

        // aturan 2: kuota antrean minimal 1 pasien
        if ($kuota < 1) {
            throw new AturanBisnisException('Kuota antrean minimal 1.');
        }

        // aturan 3: cek apakah jamnya bentrok dengan jadwal dokter yang sama di hari yang sama
        $status = $data['status'] ?? true;
        if ($status) {
            $this->cekJadwalBentrok($doctorId, $hari, $jamMulai, $jamSelesai, $excludeScheduleId);
        }
    }

    // [pbi 07] rumus pengecekan apakah dua jadwal dokter bertabrakan jamnya di hari yang sama
    public function cekJadwalBentrok(
        int $doctorId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeScheduleId = null
    ): void {
        // ambil semua jadwal aktif milik dokter tersebut di hari yang sama
        $query = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('hari', strtolower($hari))
            ->where('status', true);

        // saat edit jadwal, kecualikan jadwal dirinya sendiri supaya tidak dianggap bentrok
        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        $existingSchedules = $query->get();

        foreach ($existingSchedules as $existing) {
            $existMulai = Carbon::parse($existing->jam_mulai)->format('H:i:s');
            $existSelesai = Carbon::parse($existing->jam_selesai)->format('H:i:s');

            // rumus irisan waktu: bentrok jika jam mulai baru < jam selesai lama dan jam selesai baru > jam mulai lama
            if ($jamMulai < $existSelesai && $jamSelesai > $existMulai) {
                $formatExistMulai = Carbon::parse($existing->jam_mulai)->format('H:i');
                $formatExistSelesai = Carbon::parse($existing->jam_selesai)->format('H:i');

                throw new AturanBisnisException(
                    'Jadwal dokter bertabrakan dengan jadwal aktif yang sudah ada pada hari '.
                    ucfirst($hari)." ({$formatExistMulai} - {$formatExistSelesai})."
                );
            }
        }
    }
}
