<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function courseRecord()
    {
        return $this->belongsTo(CourseRecord::class);
    }

    public function curriculumItems()
    {
        return $this->hasMany(CurriculumItem::class)->orderBy('order');
    }
}
