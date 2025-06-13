<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Test;

class TestController extends Controller
{
    public function index($classId)
    {
        $student = auth()->user();

        $class = $student->schoolClasses()
            ->where('classes.id', $classId)
            ->firstOrFail();

        return $class->tests()
            ->select('id', 'title', 'exam_date', 'total_seconds')
            ->orderBy('exam_date')
            ->get();
    }

    public function edit($classId, $testId)
    {
        $test = Test::with(['questions'])->findOrFail($testId);

        return response()->json([
            'id' => $test->id,
            'title' => $test->title,
            'duration' => $test->total_seconds ?? 3600, // en segundos
            'questions' => QuestionResource::collection($test->questions),
        ]);
    }
}
