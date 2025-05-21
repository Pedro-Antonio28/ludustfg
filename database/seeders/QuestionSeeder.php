<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Test;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tests = Test::all();
        $questions = [
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
                'answer' => new \stdClass,
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
                'name' => 'El ___ es el satélite de la Tierra. Y su proclamador se llamaba ___.',
                'type' => 'fill_blank',
                'mark' => 1.5,
                'answer' => [
                    ['position' => 0, 'blanks' => ['Luna']],
                    ['position' => 1, 'blanks' => ['Jose Luis']],
                ],
            ],
            [
                'name' => 'El ___ procesa los datos. Donde más se guardan cosas es en la ___.',
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

        foreach ($questions as $index => $q) {
            $test = $tests[$index % $tests->count()]; // Distribuir equitativamente
            Question::create([
                'name' => $q['name'],
                'teacher_id' => 1,
                'test_id' => $test->id,
                'type' => $q['type'],
                'mark' => $q['mark'],
                'answer' => $q['answer'],
            ]);
        }
    }
}
