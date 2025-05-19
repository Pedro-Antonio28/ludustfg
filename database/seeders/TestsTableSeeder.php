<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Test;
use App\Models\SchoolClass;
use Carbon\Carbon;

class TestsTableSeeder extends Seeder
{
    public function run(): void
    {
        $class = SchoolClass::first();

        $tests = [
            [
                'title' => 'Examen de Matemáticas',
                'exam_date' => Carbon::now()->addDays(5)->toDateString(),
                'total_seconds' => 3600,
            ],
            [
                'title' => 'Examen de Historia',
                'exam_date' => Carbon::now()->addDays(12)->toDateString(),
                'total_seconds' => 2700,
            ],
            [
                'title' => 'Examen de Inglés',
                'exam_date' => Carbon::now()->addDays(20)->toDateString(),
                'total_seconds' => 3000,
            ],
            [
                'title' => 'Examen de Física',
                'exam_date' => Carbon::now()->addMonths(1)->startOfMonth()->toDateString(),
                'total_seconds' => 3600,
            ],
        ];

        foreach ($tests as $test) {
            Test::create(array_merge($test, ['class_id' => $class->id]));
        }
    }
}
