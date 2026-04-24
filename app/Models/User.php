<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'username',
        'bio',
        'avatar_url',
        'academy_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function academies()
    {
        return $this->belongsToMany(Academy::class, 'academy_user')
            ->withPivot('role', 'last_accessed_at')
            ->withTimestamps();
    }

    public function currentAcademy()
    {
        return $this->belongsTo(Academy::class, 'academy_id');
    }

    /**
     * Get the courses where the user is an expert.
     */
    public function expertCourses()
    {
        return $this->hasMany(Course::class, 'expert_id');
    }

    /**
     * Get the enrollments for the user.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class, 'expert_id');
    }

    public function certificates()
    {
        return $this->hasMany(UserCertificate::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
