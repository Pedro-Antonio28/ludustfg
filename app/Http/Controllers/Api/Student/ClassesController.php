<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentClassResource;
use App\Models\Test;
use Illuminate\Support\Facades\Log;

class ClassesController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        $classes = $student->schoolClasses()->get();

        return StudentClassResource::collection($classes);

    }

    public function results($classId)
{
    $student = auth()->user();

    $tests = Test::with(['questions.answers' => function ($q) use ($student) {
        $q->where('student_id', $student->id);
    }])
    ->where('class_id', $classId)
    ->get();

    $examData = [];

    foreach ($tests as $test) {
        $questions = $test->questions;

        $totalMark = 0;
        $maxMark = 0;
        foreach ($questions as $question) {
            $answer = $question->answers->first(); // única respuesta del alumno
            $maxMark += $question->mark;
            $totalMark += $answer?->mark ?? 0;
        }

        $nota = $maxMark > 0 ? round(($totalMark / $maxMark) * 10, 2) : 0;

        $examData[] = [
            'id' => $test->id,
            'nombre' => $test->title ?? 'Examen',
            'nota' => $nota,
            'fecha' => $test->created_at->format('d M'),
            'tiempo' => rand(25, 40),
        ];
    }
    return response()->json($examData);
}






}
