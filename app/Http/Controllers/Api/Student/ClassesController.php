<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentClassResource;
use App\Models\SchoolClass;
use App\Models\Test;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClassesController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        $classes = $student->schoolClasses()
            ->with([
                'tests.questions',
                'tests.questions.answers' => function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                }
            ])
            ->get();

        return StudentClassResource::collection(
            $classes->map(function ($class) use ($student) {
                $today = now()->startOfDay();
                $notas = [];

                foreach ($class->tests as $test) {
                    $examDate = $test->exam_date ? Carbon::parse($test->exam_date) : now()->subDay();

                    if ($examDate->greaterThan($today)) {
                        continue;
                    }

                    $testTotal = 0;
                    $testMax = 0;

                    foreach ($test->questions as $question) {
                        $questionMark = is_numeric($question->mark) ? (float) $question->mark : 1;
                        $testMax += $questionMark;
                        $answer = $question->answers->first();
                        $mark = $answer?->mark ?? 0;
                        $testTotal += $mark;
                    }

                    $nota = $testMax > 0 ? round(($testTotal / $testMax) * 10, 2) : 0;
                    $notas[] = $nota;
                }

                $average = count($notas) > 0 ? round(array_sum($notas) / count($notas), 2) : 0;

                $class->average_mark = $average;

                return $class;
            })
        );
    }

    public function results($classId)
    {
        $student = auth()->user();
        $today = now()->startOfDay();

        $tests = Test::with(['questions.answers' => function ($q) use ($student) {
            $q->where('student_id', $student->id);
        }])
            ->where('class_id', $classId)
            ->get();

        $examData = [];

        foreach ($tests as $test) {
            $examDate = $test->exam_date ? Carbon::parse($test->exam_date)->startOfDay() : null;
            if ($examDate && $examDate->greaterThanOrEqualTo($today)) {
                continue;
            }
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
                'fecha' => $test->exam_date ? Carbon::parse($test->exam_date)->format('d M') : 'Sin fecha',
                'tiempo' => rand(25, 40),
            ];
        }

        return response()->json($examData);
    }

    public function activities($classId)
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

    public function joinClass(Request $request)
    {
        $request->validate([
            'join_code' => 'required|string'
        ]);

        $class = SchoolClass::where('join_code', $request->join_code)
            ->where('join_code_expires_at', '>=', now())
            ->first();

        if (!$class) {
            return response()->json(['message' => 'Código inválido o expirado'], 404);
        }

        $student = auth()->user();

        if ($class->students()->where('student_id', $student->id)->exists()) {
            return response()->json(['message' => 'Ya estás unido a esta clase'], 400);
        }

        $class->students()->attach($student->id);

        return response()->json(['message' => 'Unido correctamente', 'class' => $class]);
    }
}
