<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'content' => 'array',
        'passing_grade' => 'integer',
    ];

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }
}
