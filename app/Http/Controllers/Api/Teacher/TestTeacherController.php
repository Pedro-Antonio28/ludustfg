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

    // Mostrar un examen con sus preguntas y etiquetas
    public function show($testId)
    {
        $teacher = auth()->user();
        $test = Test::with(['questions.tags', 'class'])
            ->findOrFail($testId);

        // Seguridad: comprobar que el profesor sea dueño de la clase
        if ($test->class->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($test);
    }

    // Actualizar un examen y re-asignar preguntas
    public function update(Request $request, $testId)
    {
        $teacher = auth()->user();
        $test = Test::with(['class', 'questions'])->findOrFail($testId);
        if ($test->class->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'exam_date'     => 'required|date',
            'total_seconds' => 'required|string',
            'question_ids'  => 'nullable|array',
            'question_ids.*'=> 'exists:questions,id'
        ]);

        // Actualizar datos básicos
        $test->update([
            'title'         => $validated['title'],
            'exam_date'     => $validated['exam_date'],
            'total_seconds' => $validated['total_seconds'],
        ]);

        $newIds = $validated['question_ids'] ?? [];

        // Desvincular preguntas que ya no estén en la lista
        $oldIds = $test->questions->pluck('id')->toArray();
        $toDetach = array_diff($oldIds, $newIds);
        if (count($toDetach)) {
            Question::whereIn('id', $toDetach)
                ->where('teacher_id', $teacher->id)
                ->update(['test_id' => null]);
        }

        // Asignar (o reasignar) preguntas nuevas al examen
        if (count($newIds)) {
            Question::whereIn('id', $newIds)
                ->where('teacher_id', $teacher->id)
                ->update(['test_id' => $test->id]);
        }

        return response()->json($test);
    }
}
