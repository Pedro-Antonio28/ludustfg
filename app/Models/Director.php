<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Director extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_img',
        'school_name',
        'school_code',
        'school_email',
        'school_tel',
        'school_type',
        'resume_days',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function schoolClasses()
    {
        return $this->hasMany(SchoolClass::class);
    }

    // En el modelo User (cuando actúa como Director)
    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'director_id');
    }
}
