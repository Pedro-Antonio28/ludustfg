<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use stdClass;

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

    public function update(Request $request, $classId, $testId)
    {
        Log::info('📥 Request completa:', $request->all());

        $data = $request->validate([
            'answers' => 'required|array',
            'time_remaining' => 'required|integer',
        ]);

        $studentId = auth()->id();

        // 🚫 Verificar si ya existe un intento
        $existing = Attempt::where('student_id', $studentId)
            ->where('test_id', $testId)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Ya has realizado este examen.'], 403);
        }

        // ✅ Registrar el primer intento
        Attempt::create([
            'student_id' => $studentId,
            'test_id' => $testId,
            'attempt_number' => 0,
        ]);

        foreach ($data['answers'] as $questionId => $rawAnswer) {
            if (is_null($rawAnswer) || $rawAnswer === '' || $rawAnswer === [] || $rawAnswer === '{}') {
                $rawAnswer = null;
            }
            $question = Question::find($questionId);
            if (!$question) continue;

            $formattedAnswer = match ($question->type) {
                'single' => ['selected' => $rawAnswer],
                'multiple' => ['selected' => $rawAnswer],
                'text' => ['response' => $rawAnswer],
                'match' => ['matches' => array_map(
                    fn($left, $right) => ['left' => $left, 'right' => $right],
                    array_keys($rawAnswer ?? []),
                    array_values($rawAnswer ?? [])
                )],
                'fill_blank', 'fill_multiple' => ['answers' => array_values($rawAnswer ?? [])],
                default => new \stdClass,
            };

            if (empty($rawAnswer)) {
                $formattedAnswer = new \stdClass;
            }

            $isCorrect = $question->isCorrectAnswer($formattedAnswer);
            $mark = match (true) {
                $isCorrect === true => $question->mark,
                $isCorrect === false => 0.0,
                default => null,
            };

            Log::info("📌 Evaluando respuesta para pregunta ID $questionId", [
                'type' => $question->type,
                'respuesta' => $formattedAnswer,
                'correcta' => $question->answer,
                'resultado' => $isCorrect,
                'mark_asignada' => $mark,
            ]);

            Answer::create([
                'student_id' => auth()->id(),
                'question_id' => $questionId,
                'answer' => is_array($formattedAnswer) ? $formattedAnswer : new stdClass,
                'mark' => $mark,
            ]);
        }


        return response()->json(['message' => 'Examen enviado con éxito']);
    }
}
