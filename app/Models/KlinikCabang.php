<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlinikCabang extends Model
{
    protected $table = 'klinik_cabang';

    protected $fillable = [
        'nama_cabang',
        'alamat',
        'nomor_telepon',
        'status'
    ];

    public function polis()
    {
        return $this->hasMany(Poli::class);
    }
}