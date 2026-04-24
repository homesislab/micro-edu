<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

use App\Traits\AcademyScoped;
use App\Traits\LogsActivity;

class Course extends Model
{
    use AcademyScoped, LogsActivity;

    protected $fillable = [
        'title', 
        'description', 
        'expert_id', 
        'passing_grade', 
        'status', 
        'price', 
        'thumbnail_path', 
        'attendance_code', 
        'zoom_link', 
        'event_time', 
        'is_recording_available', 
        'recording_url', 
        'academy_id'
    ];
    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }
    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class)->orderBy('order_index');
    }

    public function certificates()
    {
        return $this->hasMany(UserCertificate::class);
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function getTotalRequiredExercisesAttribute()
    {
        return $this->modules()->withCount(['curriculumItems as exercises_count' => function ($query) {
            $query->where('type', 'exercise');
        }])->get()->sum('exercises_count');
    }
}
