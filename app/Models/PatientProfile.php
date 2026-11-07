<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientProfile extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'jenis_pasien',
        'no_bpjs',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isLengkap(): bool
    {
        if (! $this->tanggal_lahir || ! $this->jenis_kelamin || ! $this->alamat || ! $this->jenis_pasien) {
            return false;
        }

        return $this->jenis_pasien !== 'BPJS' || ! empty($this->no_bpjs);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
