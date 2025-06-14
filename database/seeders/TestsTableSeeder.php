<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Test;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestsTableSeeder extends Seeder
{
    public function run(): void
    {
        $class = SchoolClass::first();

        $tests = [
            [
                'title' => 'Examen de Matemáticas',
                'exam_date' => Carbon::now()->subDays(10)->toDateString(),
                'total_seconds' => 3600,
            ],
            [
                'title' => 'Examen de Historia',
                'exam_date' => Carbon::now()->subDays(12)->toDateString(),
                'total_seconds' => 2700,
            ],
            [
                'title' => 'Examen de Inglés',
                'exam_date' => Carbon::now()->subDays(20)->toDateString(),
                'total_seconds' => 3000,
            ],
            [
                'title' => 'Examen de Física',
                'exam_date' => Carbon::now()->subMonth()->startOfMonth()->toDateString(),
                'total_seconds' => 3600,
            ],
        ];

        foreach ($tests as $test) {
            Test::create(array_merge($test, ['class_id' => $class->id]));
        }
    }
}
