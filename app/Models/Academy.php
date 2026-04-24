<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Academy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'is_public',
        'allow_self_enroll',
        'primary_color',
        'secondary_color',
    ];

    /**
     * The users that belong to the academy.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'academy_user')
            ->withPivot('role', 'last_accessed_at')
            ->withTimestamps();
    }

    /**
     * The courses that belong to the academy.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
