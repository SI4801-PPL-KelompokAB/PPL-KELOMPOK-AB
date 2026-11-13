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
        'klinik_cabang_id',
        'kode_poli',
        'nama_poli',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    // ──────────────────────────────────────────
    // Relasi
    // ──────────────────────────────────────────

    /**
     * Daftar jadwal dokter yang terdaftar di poli ini.
     */
    public function doctorSchedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    /**
     * Daftar antrean yang merujuk ke poli ini melalui jadwal dokter.
     */
    public function queues(): HasManyThrough
    {
        return $this->hasManyThrough(Queue::class, DoctorSchedule::class);
    }

    // ──────────────────────────────────────────
    // Scope
    // ──────────────────────────────────────────

    /**
     * Hanya poli yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', true);
    }
}
