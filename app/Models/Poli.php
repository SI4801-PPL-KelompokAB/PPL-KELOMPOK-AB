<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Poli extends Model
{
    use BelongsToCabang, HasFactory;

    protected $fillable = [
        'cabang_id',
        'kode_poli',
        'nama_poli',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ──────────────────────────────────────────
    // Relasi
    // ──────────────────────────────────────────

    /**
     * Daftar dokter yang terdaftar di poli ini.
     */
    public function dokters(): HasMany
    {
        return $this->hasMany(Dokter::class);
    }

    /**
     * Daftar antrean yang merujuk ke poli ini.
     */
    public function antreans(): HasMany
    {
        return $this->hasMany(Antrean::class);
    }

    /**
     * Jadwal praktik dokter yang terhubung dengan poli ini.
     */
    public function jadwalPraktiks(): HasManyThrough
    {
        return $this->hasManyThrough(JadwalPraktik::class, Dokter::class);
    }

    // ──────────────────────────────────────────
    // Scope
    // ──────────────────────────────────────────

    /**
     * Hanya poli yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }
}
