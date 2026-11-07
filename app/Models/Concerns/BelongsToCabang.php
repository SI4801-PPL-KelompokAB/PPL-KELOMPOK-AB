<?php

namespace App\Models\Concerns;

use App\Models\Cabang;
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

            if ($user && $user->isAdmin()) {
                $query->where($query->getModel()->getTable() . '.cabang_id', $user->cabang_id);
            }
        });

        // Data baru buatan admin otomatis masuk ke cabangnya
        static::creating(function (Model $model) {
            $user = auth()->user();

            if ($user && $user->isAdmin() && empty($model->cabang_id)) {
                $model->cabang_id = $user->cabang_id;
            }
        });
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }
}
