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
            'state' => 'required|boolean',
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
            'is_published' => $validated['state'],
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
            'state'         => 'required|boolean',
            'exam_date'     => 'required|date',
            'total_seconds' => 'required|string',
            'questions'     => 'nullable|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.mark' => 'required|numeric|min:0',
        ]);

        // Actualizar datos básicos
        $test->update([
            'title'         => $validated['title'],
            'exam_date'     => $validated['exam_date'],
            'total_seconds' => $validated['total_seconds'],
            'is_published'  => $validated['state'],
        ]);

        $new = collect($validated['questions'] ?? []);

        $newIds = $new->pluck('id')->toArray();
        $oldIds = $test->questions->pluck('id')->toArray();

        // Eliminar las que ya no están
        $toDetach = array_diff($oldIds, $newIds);
        if (count($toDetach)) {
            Question::whereIn('id', $toDetach)
                ->where('teacher_id', $teacher->id)
                ->update(['test_id' => null]);
        }

        // Asignar (o reasignar) con sus marks
        foreach ($new as $q) {
            Question::where('id', $q['id'])
                ->where('teacher_id', $teacher->id)
                ->update([
                    'test_id' => $test->id,
                    'mark' => $q['mark'],
                ]);
        }

        return response()->json($test);
    }


    public function submissions($testId)
    {
        $teacher = auth()->user();

        $test = \App\Models\Test::with('class.students')->findOrFail($testId);

        // Seguridad: solo el dueño del examen
        if ($test->class->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $students = $test->class->students->map(function ($student) use ($test) {
            $hasAttempted = \App\Models\Attempt::where('student_id', $student->id)
                ->where('test_id', $test->id)
                ->exists();

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'has_attempted' => $hasAttempted,
            ];
        });

        return response()->json($students);
    }

    public function submissionDetail($testId, $studentId)
    {
        try {
            $teacher = auth()->user();

            $test = \App\Models\Test::with(['class', 'questions'])->findOrFail($testId);

            if ($test->class->teacher_id !== $teacher->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $questions = $test->questions;
            $questionIds = $questions->pluck('id');

            $answers = \App\Models\Answer::whereIn('question_id', $questionIds)
                ->where('student_id', $studentId)
                ->get()
                ->keyBy('question_id');

            $result = $questions->map(function ($question) use ($answers) {
                $answerObj = $answers->get($question->id);
                $rawAnswer = $answerObj?->answer;

                $parsedAnswer = is_array($rawAnswer)
                    ? $rawAnswer
                    : (json_decode($rawAnswer, true) ?? $rawAnswer);

                return [
                    'question_id' => $question->id,
                    'type' => $question->type,
                    'title' => $question->name,
                    'options' => $question->answer,
                    'content' => $question->content,
                    'answer' => $parsedAnswer,
                    'mark' => $answerObj?->mark,
                ];
            });

            return response()->json([
                'student_id' => (int) $studentId,
                'test_id' => $test->id,
                'questions' => $result,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function gradeSubmission(Request $request, $testId, $studentId)
    {
        $teacher = auth()->user();

        $test = \App\Models\Test::with(['class', 'questions'])->findOrFail($testId);

        if ($test->class->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'marks' => 'required|array',
        ]);

        $questionIds = $test->questions->pluck('id');

        foreach ($data['marks'] as $questionId => $mark) {
            // Solo si la pregunta pertenece al test
            if ($questionIds->contains($questionId)) {
                \App\Models\Answer::where('question_id', $questionId)
                    ->where('student_id', $studentId)
                    ->update(['mark' => $mark]);
            }
        }

        return response()->json(['success' => true]);
    }






}
