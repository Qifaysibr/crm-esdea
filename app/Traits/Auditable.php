<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable()
    {
        static::updated(function (Model $model) {
            $criticalFields = $model->getCriticalFields();
            $changes = $model->getChanges();
            
            $interestingChanges = array_intersect_key($changes, array_flip($criticalFields));
            
            if (!empty($interestingChanges)) {
                $oldValues = array_intersect_key($model->getOriginal(), $interestingChanges);
                
                AuditLog::logAction(
                    'updated',
                    "Updated critical fields in " . class_basename($model),
                    $model,
                    $oldValues,
                    $interestingChanges
                );
            }
        });

        static::created(function (Model $model) {
            AuditLog::logAction(
                'created',
                "Created new " . class_basename($model),
                $model,
                null,
                $model->getAttributes()
            );
        });

        static::deleted(function (Model $model) {
            AuditLog::logAction(
                'deleted',
                "Deleted " . class_basename($model),
                $model,
                $model->getAttributes(),
                null
            );
        });
    }

    /**
     * Define which fields should trigger a detailed audit log on update.
     */
    protected function getCriticalFields()
    {
        return $this->criticalFields ?? [];
    }
}
