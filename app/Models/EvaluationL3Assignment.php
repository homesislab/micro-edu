<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'enrollment_id', 'curriculum_item_id', 'submission_type', 'file_path', 'link_url', 
    'description', 'status', 'expert_id', 'expert_notes', 
    'reviewed_at', 'submitted_at'
])]
class EvaluationL3Assignment extends Model
{
    protected $casts = [
        'reviewed_at' => 'datetime',
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

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function rubricScore()
    {
        return $this->hasOne(RubricScore::class, 'assignment_id');
    }
}
