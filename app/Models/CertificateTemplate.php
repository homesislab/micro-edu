<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_id',
        'name',
        'content_html',
        'background_image_path',
        'is_default',
    ];

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function certificates()
    {
        return $this->hasMany(UserCertificate::class, 'template_id');
    }
}
