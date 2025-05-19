<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    public function run(): void
    {
        $studentIds = [1, 2];
        $questions = Question::all();

        foreach ($questions as $question) {
            foreach ($studentIds as $studentId) {
                $response = $this->generateFakeAnswer($question);
                $mark = $this->calculateMark($question, $response);

                Answer::create([
                    'student_id' => $studentId,
                    'question_id' => $question->id,
                    'answer' => $response,
                    'mark' => $mark,
                ]);
            }
        }
    }

    private function generateFakeAnswer($question): array
    {
        switch ($question->type) {
            case 'single':
                return ['selected' => rand(0, count($question->answer['options']) - 1)];

            case 'multiple':
                return ['selected' => collect(range(0, count($question->answer['options']) - 1))
                    ->shuffle()->take(rand(1, 3))->values()->all()];

            case 'text':
                return ['response' => 'Explicación escrita del alumno.'];

            case 'match':
                return ['matches' => $question->answer['pairs']];

            case 'fill_blank':
                return [
                    'answers' => collect($question->answer)->map(fn ($b) => $b['blanks'][0])->all(),
                ];

            case 'fill_multiple':
                return [
                    'answers' => collect($question->answer)->map(
                        fn ($blank) => $blank['options'][$blank['correct']]
                    )->all(),
                ];

            default:
                return [];
        }
    }

    private function calculateMark($question, $answer): ?float
    {
        $totalMark = $question->mark;

        switch ($question->type) {
            case 'single':
                return ($answer['selected'] ?? null) === ($question->answer['correct'] ?? null)
                    ? $totalMark : 0.0;

            case 'multiple':
                $selected = collect($answer['selected'] ?? []);
                $correct = collect($question->answer['correct'] ?? []);

                return $selected->sort()->values()->all() === $correct->sort()->values()->all()
                    ? $totalMark : 0.0;

            case 'match':
                $correctPairs = collect($question->answer['pairs']);
                $studentPairs = collect($answer['matches'] ?? []);
                $total = $correctPairs->count();
                $correctCount = $correctPairs->filter(fn ($pair, $i) => isset($studentPairs[$i]) &&
                    $pair['left'] === $studentPairs[$i]['left'] &&
                    $pair['right'] === $studentPairs[$i]['right']
                )->count();

                return $total > 0 ? round(($correctCount / $total) * $totalMark, 2) : 0.0;

            case 'fill_blank':
                $correctBlanks = collect($question->answer)->pluck('blanks')->flatten()->map('strtolower');
                $studentAnswers = collect($answer['answers'] ?? [])->map('strtolower');
                $total = $correctBlanks->count();
                $correctCount = $correctBlanks->filter(function ($val, $i) use ($studentAnswers) {
                    return isset($studentAnswers[$i]) && $val === $studentAnswers[$i];
                })->count();

                return $total > 0 ? round(($correctCount / $total) * $totalMark, 2) : 0.0;

            case 'fill_multiple':
                $correctAnswers = collect($question->answer)->map(
                    fn ($b) => $b['options'][$b['correct']]
                )->map('strtolower');
                $studentAnswers = collect($answer['answers'] ?? [])->map('strtolower');
                $total = $correctAnswers->count();
                $correctCount = $correctAnswers->filter(function ($val, $i) use ($studentAnswers) {
                    return isset($studentAnswers[$i]) && $val === $studentAnswers[$i];
                })->count();

                return $total > 0 ? round(($correctCount / $total) * $totalMark, 2) : 0.0;

            case 'text':
                return null; // requiere revisión manual

            default:
                return 0.0;
        }
    }
}
