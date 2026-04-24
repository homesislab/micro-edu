<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['enrollment_id', 'rating', 'review', 'submitted_at'])]
class EvaluationL1Feedback extends Model
{
    protected $table = 'evaluation_l1_feedback';

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
