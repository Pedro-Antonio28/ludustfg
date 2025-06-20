<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentClassResource;
use App\Models\SchoolClass;
use App\Models\Test;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClassesController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();

        $today = now()->startOfDay();

        $classes = $teacher->schoolClasses()
            ->with([
                'tests.questions.answers'
            ])
            ->paginate(6);

        return StudentClassResource::collection(
            $classes->through(function ($class) use ($today) {
                $notas = [];

                foreach ($class->tests as $test) {
                    $examDate = $test->exam_date ? Carbon::parse($test->exam_date)->startOfDay() : null;

                    // Ignorar exámenes de hoy o futuros
                    if ($examDate && $examDate->greaterThanOrEqualTo($today)) {
                        continue;
                    }

                    $total = 0;
                    $max = 0;

                    foreach ($test->questions as $question) {
                        foreach ($question->answers as $answer) {
                            $max += $question->mark;
                            if ($question->isCorrectAnswer($answer->answer)) {
                                $total += $question->mark;
                            }
                        }
                    }

                    $nota = $max > 0 ? round(($total / $max) * 10, 2) : 0;
                    $notas[] = $nota;
                }

                $class->average_mark = count($notas) > 0 ? round(array_sum($notas) / count($notas), 2) : 0;

                return $class;
            })
        );
    }

    public function activities($classId)
    {
        $teacher = auth()->user();

        $class = $teacher->schoolClasses()
            ->where('classes.id', $classId)
            ->firstOrFail();

        return $class->tests()
            ->select('id', 'title', 'exam_date', 'total_seconds', 'is_published')
            ->orderBy('exam_date')
            ->get();
    }

    public function results($classId)
    {
        $today = now()->startOfDay();

        $tests = Test::with(['questions.answers'])
            ->where('class_id', $classId)
            ->get();

        $examData = [];

        foreach ($tests as $test) {
            $examDate = $test->exam_date ? \Carbon\Carbon::parse($test->exam_date)->startOfDay() : null;

            // Ignorar exámenes futuros
            if ($examDate && $examDate->greaterThanOrEqualTo($today)) {
                continue;
            }

            $questions = $test->questions;
            $studentScores = [];
            $studentTimes = [];

            $answersGroupedByStudent = [];

            foreach ($questions as $question) {
                foreach ($question->answers as $answer) {
                    $answersGroupedByStudent[$answer->student_id][] = [
                        'mark' => $question->mark,
                        'correct' => $question->isCorrectAnswer($answer->answer) ?? false
                    ];
                }
            }

            foreach ($answersGroupedByStudent as $studentId => $entries) {
                $total = 0;
                $max = 0;

                foreach ($entries as $entry) {
                    $max += $entry['mark'];
                    if ($entry['correct']) {
                        $total += $entry['mark'];
                    }
                }

                $notaEstudiante = $max > 0 ? ($total / $max) * 10 : 0;
                $studentScores[] = round($notaEstudiante, 2);
                $studentTimes[] = rand(25, 40);
            }

            $mediaNotas = count($studentScores) > 0
                ? round(array_sum($studentScores) / count($studentScores), 2)
                : 0;

            $mediaTiempo = count($studentTimes) > 0
                ? round(array_sum($studentTimes) / count($studentTimes))
                : 0;

            $examData[] = [
                'id' => $test->id,
                'nombre' => $test->title ?? 'Examen',
                'nota' => $mediaNotas,
                'fecha' => $test->created_at->format('d M'),
                'tiempo' => $mediaTiempo,
            ];
        }

        return response()->json($examData);
    }

    public function generateJoinCode($classId)
    {
        $teacher = auth()->user();

        $class = SchoolClass::where('id', $classId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        if (
            !$class->join_code ||
            !$class->join_code_expires_at ||
            $class->join_code_expires_at->isPast()
        ) {
            $class->generateJoinCode();
        }

        return response()->json([
            'code' => $class->join_code,
            'expires_at' => $class->join_code_expires_at,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
        ]);

        $teacher = auth()->user();

        $class = SchoolClass::create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? null,
            'teacher_id' => $teacher->id,
        ]);

        return response()->noContent(201);
    }
}
