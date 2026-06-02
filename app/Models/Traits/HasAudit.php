<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HasAudit
{
    public static function bootHasAudit(): void
    {
        static::creating(function (Model $model) {
            $actor = static::currentActor();
            $model->created_by ??= $actor;
            $model->updated_by ??= $actor;
        });

        static::updating(function (Model $model) {
            $model->updated_by = static::currentActor();
        });

        static::deleting(function (Model $model) {
            if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))) {
                DB::table($model->getTable())
                    ->where($model->getKeyName(), $model->getKey())
                    ->update(['deleted_by' => static::currentActor()]);
            }
        });
    }

    public static function currentActor(): string
    {
        return app()->bound('audit.actor') ? (string) app('audit.actor') : 'system';
    }
}
