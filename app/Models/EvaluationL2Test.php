<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['enrollment_id', 'curriculum_item_id', 'type', 'answers', 'score', 'submitted_at'])]
class EvaluationL2Test extends Model
{
    protected $casts = [
        'answers' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function curriculumItem()
    {
        return $this->belongsTo(CurriculumItem::class);
    }
}
