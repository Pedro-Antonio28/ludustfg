<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    protected $fillable = ['student_id', 'test_id', 'attempt_number'];
}
