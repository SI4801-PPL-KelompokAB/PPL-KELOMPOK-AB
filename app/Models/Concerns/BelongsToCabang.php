<?php

namespace App\Models\Concerns;

use App\Models\KlinikCabang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCabang
{
    protected static function bootBelongsToCabang(): void
    {
        // Admin hanya melihat data cabangnya sendiri
        static::addGlobalScope('cabang', function (Builder $query) {
            $user = auth()->user();

            if ($user && $user->isAdmin() && $user->klinik_cabang_id) {
                $query->where($query->getModel()->getTable().'.klinik_cabang_id', $user->klinik_cabang_id);
            }
        });

        // Data baru buatan admin otomatis masuk ke cabangnya
        static::creating(function (Model $model) {
            $user = auth()->user();

            if (empty($model->klinik_cabang_id)) {
                $model->klinik_cabang_id = $user?->klinik_cabang_id ?? KlinikCabang::first()?->id ?? 1;
            }
        });
    }

    public function klinikCabang(): BelongsTo
    {
        return $this->belongsTo(KlinikCabang::class);
    }
}
