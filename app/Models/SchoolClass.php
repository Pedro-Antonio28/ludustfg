<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'image_url',
    ];

    protected $casts = [
        'join_code_expires_at' => 'datetime',
    ];

    public function generateJoinCode()
    {
        $this->join_code = strtoupper(Str::random(6));
        $this->join_code_expires_at = now()->addWeek();
        $this->save();
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_student', 'class_id', 'student_id');
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'class_id');
    }
}
