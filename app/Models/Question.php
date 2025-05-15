<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'teacher_id',
        'test_id',
        'type',
        'answer',
        'mark'
    ];

    protected $casts = [
        'answer' => 'array',
    ];
}
