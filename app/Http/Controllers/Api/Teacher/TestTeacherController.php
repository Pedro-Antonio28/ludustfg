<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\Question;

class TestTeacherController extends Controller
{
    public function store(Request $request)
    {
        $teacher = auth()->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'total_seconds' => 'required|string',
            'exam_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        $exam = Test::create([
            'title' => $validated['title'],
            'total_seconds' => $validated['total_seconds'],
            'exam_date' => $validated['exam_date'],
            'class_id' => $validated['class_id'],
        ]);

        // Asociar preguntas al examen
        if (!empty($validated['question_ids'])) {
            Question::whereIn('id', $validated['question_ids'])
                ->where('teacher_id', $teacher->id) // Seguridad: solo sus preguntas
                ->update(['test_id' => $exam->id]);
        }

        return response()->json($exam, 201);
    }
}
