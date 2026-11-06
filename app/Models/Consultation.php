<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    /** Status yang masih memakai kuota dan dianggap aktif. */
    public const STATUS_AKTIF = ['pending', 'accepted', 'ongoing'];

    public const LABEL_STATUS = [
        'pending'   => 'Menunggu',
        'accepted'  => 'Diterima',
        'ongoing'   => 'Berlangsung',
        'completed' => 'Selesai',
        'rejected'  => 'Ditolak',
    ];

    protected $fillable = [
        'nomor_konsultasi',
        'patient_profile_id',
        'doctor_id',
        'doctor_schedule_id',
        'tanggal_konsultasi',
        'subject',
        'keluhan',
        'status',
    ];

    protected $casts = [
        'tanggal_konsultasi' => 'date',
    ];

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function schedule()
    {
        return $this->belongsTo(DoctorSchedule::class, 'doctor_schedule_id');
    }

    public function messages()
    {
        return $this->hasMany(ConsultationMessage::class)->orderBy('id');
    }

    /** $konsultasi->pasien->name */
    public function getPasienAttribute(): ?User
    {
        return $this->patientProfile?->user;
    }

    /** $konsultasi->dokter->name */
    public function getDokterAttribute(): ?User
    {
        return $this->doctor?->user;
    }

    /** $konsultasi->poli->nama_poli (diambil dari jadwal dokter) */
    public function getPoliAttribute(): ?Poli
    {
        return $this->schedule?->poli;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::LABEL_STATUS[$this->status] ?? ucfirst($this->status);
    }
}