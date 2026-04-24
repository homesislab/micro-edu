<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricTemplate extends Model
{
    protected $fillable = ['expert_id', 'course_id', 'name', 'criteria_json', 'is_default'];

    protected $casts = [
        'criteria_json' => 'array',
        'is_default' => 'boolean',
    ];

    public function expert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(RubricScore::class);
    }
}
