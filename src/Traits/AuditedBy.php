<?php

namespace Keysoft\HelperLibrary\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Keysoft\HelperLibrary\Models\Tenant\MsUser;

trait AuditedBy
{

    public static function bootAuditedBy(): void
    {
        $payload = request()->attributes->get('jwt_payload');
        $userId = $payload['userId'] ?? Auth::id() ?? null;

        if (!$userId) {
            return;
        }

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

        static::creating(function ($model) use ($userId) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = $userId;
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

        static::updating(function ($model) use ($userId) {
            if (Schema::hasColumn($model->getTable(), 'updated_by') && !$model->isDirty('updated_by')) {
                $model->updated_by = $userId;
            }
        });

        static::saving(function ($model) use ($userId) {
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = $userId;
            }
        });

        static::deleting(function ($model) use ($userId) {
            if (Schema::hasColumn($model->getTable(), 'deleted_by')) {
                $model->deleted_by = $userId;

                $model->newQuery()
                    ->where($model->getKeyName(), $model->getKey())
                    ->update(['deleted_by' => $userId]);
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
        return $this->belongsTo(MsUser::class, 'created_by', 'username');
    }

    public function getCreatorNameAttribute()
    {
        return $this->createdBy?->username ?? $this->created_by;
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(MsUser::class, 'updated_by', 'username');
    }

    public function getUpdatedNameAttribute()
    {
        return $this->updatedBy?->username ?? $this->updated_by;
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(MsUser::class, 'deleted_by', 'username');
    }

    public function getDeletedNameAttribute()
    {
        return $this->deletedBy?->username ?? $this->deleted_by;
    }

}
