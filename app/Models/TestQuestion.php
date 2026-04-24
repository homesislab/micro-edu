<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestQuestion extends Model
{
    protected $fillable = [
        'course_id',
        'type',
        'question_text',
        'options',
        'correct_key',
        'order',
        'weight',
        'competency_tags',
    ];

    protected $casts = [
        'options'          => 'array',
        'competency_tags'  => 'array',
        'weight'           => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

