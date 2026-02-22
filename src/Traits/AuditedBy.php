<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait AuditedBy
{

    public static function bootAuditedBy(): void
    {
        $clearCache = function ($model) {
            // Cek apakah model memiliki method 'getCacheKeys'
            if (method_exists($model, 'getCacheKeys')) {
                $keys = $model->getCacheKeys();

                // Support return string tunggal atau array
                foreach ((array) $keys as $key) {
                    Cache::forget($key);
                }
            }
        };

        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::id() ?? null;
            }

            if (
                Schema::hasColumn($model->getTable(), 'sort_order') &&
                is_null($model->sort_order)
            ) {
                DB::transaction(function () use ($model) {

                    $last = DB::table($model->getTable())
                        ->select('sort_order')
                        ->orderByDesc('sort_order')
                        ->lockForUpdate()
                        ->first();

                    $model->sort_order = $last?->sort_order + 1 ?? 0;
                });
            }
        });

        static::updating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'updated_by') && !$model->isDirty('updated_by')) {
                $model->updated_by = Auth::id() ?? null;
            }
        });

        static::saving(function ($model) {
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = Auth::id() ?? null;
            }
        });

        static::deleting(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'deleted_by')) {

                $model->deleted_by = Auth::id();

                $model->newQuery()
                    ->where($model->getKeyName(), $model->getKey())
                    ->update(['deleted_by' => Auth::id()]);
            }
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {

            static::restoring(function ($model) {

                if (Schema::hasColumn($model->getTable(), 'deleted_by')) {
                    $model->newQuery()
                        ->where($model->getKeyName(), $model->getKey())
                        ->update(['deleted_by' => null]);
                }
            });

            static::restored($clearCache);
        }

        /*
        |--------------------------------------------------------------------------
        | AFTER COMMIT EVENTS (CLEAR CACHE)
        |--------------------------------------------------------------------------
        */

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCreatorNameAttribute()
    {
        return $this->createdBy?->name;
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getUpdatedNameAttribute()
    {
        return $this->updatedBy?->name;
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getDeletedNameAttribute()
    {
        return $this->deletedBy?->name;
    }

}
