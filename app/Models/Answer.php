<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'answer',
        'student_id',
        'question_id',
        'mark'
    ];

    protected $casts = [
        'answer' => 'array',
    ];
}
