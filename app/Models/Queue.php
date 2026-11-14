<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'patient_profile_id',
        'doctor_schedule_id',
        'tanggal_antrean',
        'nomor_antrean',
        'status',
    ];

    protected $casts = [
        'tanggal_antrean' => 'date',
    ];

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function doctorSchedule()
    {
        return $this->belongsTo(DoctorSchedule::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_antrean', today());
    }
}