<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Test;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentClassWithTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear profesor
        $teacher = Teacher::first() ?? Teacher::factory()->create([
            'name' => 'Jose Luis Torrente',
            'email' => 'torrente@profesor.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Crear clase
        $class = SchoolClass::factory()->create([
            'name' => 'Clase de prueba',
            'teacher_id' => $teacher->id,
        ]);

        // 3. Crear alumno directamente (eliminando anterior si ya existe por email)
        Student::where('email', 'alumno@test.com')->delete();

        $studentData = [
            ['name' => 'Alumno Test', 'email' => 'alumno@test.com'],
            ['name' => 'Ana Ejemplo', 'email' => 'ana@demo.com'],
            ['name' => 'Luis Prueba', 'email' => 'luis@demo.com'],
        ];

        // 4. Relacionar alumno con la clase
        $students = [];

        foreach ($studentData as $data) {
            Student::where('email', $data['email'])->delete();
            $student = Student::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
            ]);
            $student->schoolClasses()->syncWithoutDetaching([$class->id]);
            $students[] = $student;
        }

        $exams = [
            ['title' => 'Matemáticas', 'exam_date' => Carbon::now()->subDays(5)],
            ['title' => 'Historia', 'exam_date' => Carbon::now()->subDays(10)],
            ['title' => 'Inglés', 'exam_date' => Carbon::now()->subDays(15)],
        ];

        $questionTemplates = [
            [
                'name' => '¿Cuál es la capital de Francia?',
                'type' => 'single',
                'mark' => 1.0,
                'answer' => [
                    'correct' => 1,
                    'options' => ['Londres', 'París', 'Madrid', 'Berlín'],
                ],
            ],
            [
                'name' => 'Selecciona los lenguajes de programación',
                'type' => 'multiple',
                'mark' => 2.0,
                'answer' => [
                    'correct' => [1, 2],
                    'options' => ['HTML', 'Python', 'JavaScript', 'CSS'],
                ],
            ],
            [
                'name' => 'Explica el proceso de fotosíntesis',
                'type' => 'text',
                'mark' => 2.0,
                'answer' => new \stdClass(),
            ],
            [
                'name' => 'Relaciona los países con sus capitales',
                'type' => 'match',
                'mark' => 1.5,
                'answer' => [
                    'pairs' => [
                        ['left' => 'España', 'right' => 'Madrid'],
                        ['left' => 'Italia', 'right' => 'Roma'],
                    ],
                ],
            ],
            [
                'name' => 'El [🔲1] es el satélite de la Tierra. Y su proclamador se llamaba [🔲2].',
                'type' => 'fill_blank',
                'mark' => 1.5,
                'answer' => [
                    ['position' => 0, 'blanks' => ['Luna']],
                    ['position' => 1, 'blanks' => ['Jose Luis']],
                ],
            ],
            [
                'name' => 'El [🔲1] procesa los datos. Donde más se guardan cosas es en la [🔲2].',
                'type' => 'fill_multiple',
                'mark' => 2.0,
                'answer' => [
                    [
                        'position' => 0,
                        'correct' => 0,
                        'options' => ['CPU', 'Monitor', 'Teclado'],
                    ],
                    [
                        'position' => 1,
                        'correct' => 1,
                        'options' => ['CPU', 'Cache', 'LocalStorage'],
                    ],
                ],
            ],
        ];

        foreach ($exams as $examData) {
            $test = Test::create([
                'title' => $examData['title'],
                'exam_date' => $examData['exam_date'],
                'total_seconds' => 3600,
                'class_id' => $class->id,
            ]);

            foreach ($questionTemplates as $q) {
                $question = Question::create([
                    'name' => $q['name'],
                    'teacher_id' => $teacher->id,
                    'test_id' => $test->id,
                    'type' => $q['type'],
                    'mark' => $q['mark'],
                    'answer' => $q['answer'],
                ]);

                foreach ($students as $student) {
                    $studentAnswer = $this->generateFakeAnswer($question);
                    $mark = $this->calculateMark($question, $studentAnswer);

                    Answer::create([
                        'student_id' => $student->id,
                        'question_id' => $question->id,
                        'answer' => $studentAnswer,
                        'mark' => $mark,
                    ]);
                }
            }
        }

        $this->command->info("✅ Seeder completado con {$class->name}, 3 alumnos, exámenes, preguntas y respuestas.");
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
                return ['matches' => $question->answer['pairs'] ?? []];

            case 'fill_blank':
                return [
                    'answers' => collect($question->answer)->map(fn($b) => $b['blanks'][0])->all(),
                ];

            case 'fill_multiple':
                return [
                    'answers' => collect($question->answer)->map(
                        fn($blank) => $blank['options'][$blank['correct']]
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
                $correctCount = $correctPairs->filter(
                    fn($pair, $i) =>
                    isset($studentPairs[$i]) &&
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
                    fn($b) => $b['options'][$b['correct']]
                )->map('strtolower');
                $studentAnswers = collect($answer['answers'] ?? [])->map('strtolower');
                $total = $correctAnswers->count();
                $correctCount = $correctAnswers->filter(function ($val, $i) use ($studentAnswers) {
                    return isset($studentAnswers[$i]) && $val === $studentAnswers[$i];
                })->count();

                return $total > 0 ? round(($correctCount / $total) * $totalMark, 2) : 0.0;

            case 'text':
                return null;

            default:
                return 0.0;
        }
    }
}
