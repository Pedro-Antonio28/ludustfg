<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Test;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentClassWithTestsSeeder extends Seeder
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

        $student = Student::create([
            'name' => 'Alumno Test',
            'email' => 'alumno@test.com',
            'password' => Hash::make('password'),
        ]);

        // 4. Relacionar alumno con la clase
        $student->schoolClasses()->syncWithoutDetaching([$class->id]);

        // 5. Crear exámenes
        $exams = [
            ['title' => 'Matemáticas', 'exam_date' => Carbon::now()->addDays(5)],
            ['title' => 'Historia', 'exam_date' => Carbon::now()->addDays(10)],
            ['title' => 'Inglés', 'exam_date' => Carbon::now()->addDays(15)],
        ];

        foreach ($exams as $exam) {
            Test::create([
                'title' => $exam['title'],
                'exam_date' => $exam['exam_date'],
                'total_seconds' => 3600,
                'class_id' => $class->id,
            ]);
        }

        $this->command->info("✅ Alumno creado con ID {$student->id} y vinculado a clase ID {$class->id}");
    }
}
