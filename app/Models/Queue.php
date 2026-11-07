<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    public const STATUS_WAITING = 'waiting';

    public const STATUS_CALLED = 'called';

    public const STATUS_SERVING = 'serving';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SKIPPED = 'skipped';

    public const ACTIVE_STATUSES = [
        self::STATUS_WAITING,
        self::STATUS_CALLED,
        self::STATUS_SERVING,
    ];

    protected $fillable = [
        'patient_profile_id',
        'doctor_schedule_id',
        'tanggal_antrean',
        'nomor_antrean',
        'status',
    ];

    protected $casts = [
        'tanggal_antrean' => 'date',
        'nomor_antrean' => 'integer',
    ];

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function doctorSchedule()
    {
        return $this->belongsTo(DoctorSchedule::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function getFormattedNomorAttribute(): string
    {
        return str_pad($this->nomor_antrean, 3, '0', STR_PAD_LEFT);
    }

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
