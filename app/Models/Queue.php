<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    // daftar konstanta status antrean di database
    public const STATUS_WAITING = 'waiting';

    public const STATUS_CALLED = 'called';

    public const STATUS_SERVING = 'serving';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SKIPPED = 'skipped';

    // status yang dianggap masih aktif (pasien belum boleh ambil antrean baru)
    public const ACTIVE_STATUSES = [
        self::STATUS_WAITING,
        self::STATUS_CALLED,
        self::STATUS_SERVING,
    ];

    // kolom tabel queues yang boleh diisi
    protected $fillable = [
        'patient_profile_id',
        'doctor_schedule_id',
        'tanggal_antrean',
        'nomor_antrean',
        'status',
    ];

    // konversi tipe data otomatis
    protected $casts = [
        'tanggal_antrean' => 'date',
        'nomor_antrean' => 'integer',
    ];

    // relasi antrean ke profil pasien
    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class);
    }

    // relasi antrean ke jadwal praktik dokter
    public function doctorSchedule()
    {
        return $this->belongsTo(DoctorSchedule::class);
    }

    // cek apakah antrean ini masih berstatus aktif
    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    // ubah angka nomor antrean (misal 1) jadi format 3 digit (001) untuk tiket
    public function getFormattedNomorAttribute(): string
    {
        return str_pad($this->nomor_antrean, 3, '0', STR_PAD_LEFT);
    }

    // terjemahkan status bahasa inggris di database jadi label bahasa indonesia di layar
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING => 'Menunggu',
            self::STATUS_CALLED => 'Dipanggil',
            self::STATUS_SERVING => 'Sedang Dilayani',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_SKIPPED => 'Dilewati',
            default => ucfirst($this->status),
        };
    }
}
