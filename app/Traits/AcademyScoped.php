<?php

namespace App\Traits;

use App\Models\Scopes\AcademyScope;

trait AcademyScoped
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::addGlobalScope(new AcademyScope);

        static::creating(function ($model) {
            if (auth()->check() && ! $model->academy_id) {
                $model->academy_id = auth()->user()->academy_id;
            }
        });
    }
}
