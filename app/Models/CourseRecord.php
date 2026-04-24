<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRecord extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }

    public function instructors()
    {
        return $this->belongsToMany(User::class, 'course_record_instructor');
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }
}
