<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AcademyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Super admins (no academy_id) can see everything, other contexts are scoped by academy_id
            if ($user->academy_id) {
                $builder->where($model->getTable() . '.academy_id', $user->academy_id);
            }
        }
    }
}
