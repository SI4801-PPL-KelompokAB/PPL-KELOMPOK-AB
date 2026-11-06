<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $fillable = ['nama', 'spesialisasi', 'no_telp', 'email'];

    public function jadwal()
    {
        return $this->hasMany(JadwalDokter::class);
    }
}