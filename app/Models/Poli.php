<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    protected $table = 'polis';

    protected $fillable = [
        'klinik_cabang_id',
        'nama_poli',
        'deskripsi',
        'status'
    ];

    public function klinikCabang()
    {
        return $this->belongsTo(KlinikCabang::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }
}