<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'content_json' => 'array',
        'rubric_json' => 'array',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
