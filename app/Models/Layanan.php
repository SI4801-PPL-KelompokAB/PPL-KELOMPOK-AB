<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use BelongsToCabang, HasFactory;

    protected $table = 'layanans';

    protected $fillable = [
        'klinik_cabang_id',
        'poli_id',
        'kode_layanan',
        'nama_layanan',
        'tarif_umum',
        'tarif_bpjs',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'tarif_umum' => 'decimal:2',
        'tarif_bpjs' => 'decimal:2',
    ];

    public function klinikCabang()
    {
        return $this->belongsTo(KlinikCabang::class, 'klinik_cabang_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    public function getTarifUmumFormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->tarif_umum, 0, ',', '.');
    }

    public function getTarifBpjsFormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->tarif_bpjs, 0, ',', '.');
    }
}
