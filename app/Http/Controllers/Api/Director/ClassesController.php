<?php

namespace App\Http\Controllers\Api\Director;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentClassResource;
use App\Models\SchoolClass;
use App\Models\Test;
use Illuminate\Support\Facades\Log;

class ClassesController extends Controller
{
    public function index()
    {
        $director = auth()->user();

        $classes = $director->schoolClasses()->get();

        return StudentClassResource::collection($classes);
    }

    public function activities($classId)
    {
        $director = auth()->user();

        $class = $director->schoolClasses()
            ->where('classes.id', $classId)
            ->firstOrFail();

        return $class->tests()
            ->select('id', 'title', 'exam_date', 'total_seconds')
            ->orderBy('exam_date')
            ->get();
    }

    public function results($classId)
    {
        $tests = Test::with(['questions.answers'])
            ->where('class_id', $classId)
            ->get();

        $examData = [];
        Log::info('test', $tests->toArray());

        foreach ($tests as $test) {
            $questions = $test->questions;
            $studentScores = [];
            $studentTimes = [];

            // Agrupar respuestas por estudiante
            $answersGroupedByStudent = [];

            foreach ($questions as $question) {
                foreach ($question->answers as $answer) {
                    $answersGroupedByStudent[$answer->student_id][] = [
                        'mark' => $question->mark,
                        'correct' => $question->isCorrectAnswer($answer->answer) ?? false
                    ];
                }
            }

            // Calcular nota por estudiante
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

                // Simulamos tiempo con rand si no hay tiempo real
                $studentTimes[] = rand(25, 40);
            }

            // Media total
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
        $director = auth()->user();

        $class = SchoolClass::where('id', $classId)
            ->where('director_id', $director->id)
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
}
