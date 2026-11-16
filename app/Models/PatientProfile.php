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

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Memeriksa kelengkapan identitas profil pasien.
     */
    public function isLengkap(): bool
    {
        if (empty($this->tanggal_lahir) || empty($this->jenis_kelamin) || empty(trim($this->alamat ?? ''))) {
            return false;
        }

        if (! empty($this->jenis_pasien) && $this->jenis_pasien === 'BPJS') {
            return ! empty($this->no_bpjs);
        }

        return true;
    }
}
