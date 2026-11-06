<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id',
        'poli_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'kuota',
        'status'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}