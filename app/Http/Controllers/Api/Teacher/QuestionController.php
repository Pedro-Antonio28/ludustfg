<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function indexBank()
    {
        $questions = Question::whereNull('test_id')
            ->with('tags')
            ->orderBy('created_at', 'desc')
            ->get();

        return QuestionResource::collection($questions);
    }
}
