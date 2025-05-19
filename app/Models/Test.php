<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
use HasFactory;

protected $fillable = [
'title',
'total_seconds',
'exam_date',
'class_id',
];

public function class()
{
return $this->belongsTo(SchoolClass::class, 'class_id');
}

public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
