<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['course_id', 'type', 'question_text', 'options', 'correct_key', 'order'])]
class TestQuestion extends Model
{
    protected $casts = [
        'options' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
