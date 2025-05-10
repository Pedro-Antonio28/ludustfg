<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    use HasFactory;

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
        'membership_start',
    ];
}
