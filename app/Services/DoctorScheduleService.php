<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\DoctorSchedule;
use Carbon\Carbon;

class DoctorScheduleService
{
    /**
     * Daftar hari yang valid.
     */
    public const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

    /**
     * Membuat jadwal baru setelah validasi aturan bisnis.
     *
     * @throws AturanBisnisException
     */
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

    /**
     * Memperbarui jadwal setelah validasi aturan bisnis.
     *
     * @throws AturanBisnisException
     */
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

    /**
     * Mengubah status aktif / nonaktif jadwal (tidak dihapus).
     *
     * @throws AturanBisnisException
     */
    public function toggleStatus(DoctorSchedule $schedule): DoctorSchedule
    {
        $statusBaru = ! $schedule->status;

        if ($statusBaru) {
            // Jika ingin mengaktifkan kembali, pastikan tidak bentrok dengan jadwal aktif lain
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

    /**
     * Validasi aturan bisnis jadwal praktik.
     *
     * @throws AturanBisnisException
     */
    public function validasiAturanJadwal(array $data, ?int $excludeScheduleId = null): void
    {
        $jamMulai = Carbon::parse($data['jam_mulai'])->format('H:i:s');
        $jamSelesai = Carbon::parse($data['jam_selesai'])->format('H:i:s');
        $hari = strtolower($data['hari']);
        $kuota = (int) ($data['kuota'] ?? 0);
        $doctorId = (int) $data['doctor_id'];

        // Aturan 1: Jam selesai harus setelah jam mulai
        if ($jamSelesai <= $jamMulai) {
            throw new AturanBisnisException('Jam selesai harus setelah jam mulai.');
        }

        // Aturan 2: Kuota minimal 1
        if ($kuota < 1) {
            throw new AturanBisnisException('Kuota antrean minimal 1.');
        }

        // Aturan 3: Jadwal satu dokter di hari yang sama tidak boleh bertabrakan
        // Hanya cek bentrok jika status jadwal aktif
        $status = $data['status'] ?? true;
        if ($status) {
            $this->cekJadwalBentrok($doctorId, $hari, $jamMulai, $jamSelesai, $excludeScheduleId);
        }
    }

    /**
     * Memeriksa apakah ada jadwal yang bertabrakan untuk dokter dan hari yang sama.
     *
     * @throws AturanBisnisException
     */
    public function cekJadwalBentrok(
        int $doctorId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeScheduleId = null
    ): void {
        $query = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('hari', strtolower($hari))
            ->where('status', true);

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        $existingSchedules = $query->get();

        foreach ($existingSchedules as $existing) {
            $existMulai = Carbon::parse($existing->jam_mulai)->format('H:i:s');
            $existSelesai = Carbon::parse($existing->jam_selesai)->format('H:i:s');

            // Rentang [A, B] dan [C, D] bentrok jika A < D dan B > C
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
