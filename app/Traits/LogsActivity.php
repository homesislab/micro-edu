<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            $model->logActivity('created');
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            
            // Don't log if only timestamps changed
            unset($changes['updated_at']);
            if (empty($changes)) return;

            $properties = [
                'old' => array_intersect_key($model->getOriginal(), $changes),
                'new' => $changes,
            ];

            $model->logActivity('updated', null, $properties);
        });

        static::deleted(function (Model $model) {
            $model->logActivity('deleted');
        });
    }

    public function logActivity(string $action, string $description = null, array $properties = null)
    {
        ActivityLog::log(
            $action,
            $this,
            $description ?? $this->getActivityDescription($action),
            $properties
        );
    }

    protected function getActivityDescription(string $action): string
    {
        $name = class_basename($this);
        return "{$name} was {$action}";
    }
}
