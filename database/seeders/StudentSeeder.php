<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = SchoolClass::all();

        foreach ($classes as $class) {
            // Crea 2 estudiantes por clase
            $students = Student::factory()->count(2)->create();

            // Asocia a la clase
            $class->students()->attach($students->pluck('id')->toArray());

        }

        // Crear alumno manual para pruebas
        $demoStudent = Student::create([
            'name' => 'Alumno Demo',
            'email' => 'alumno@demo.com',
            'password' => bcrypt('password123'),
        ]);

        $class = SchoolClass::first();
        if ($class) {
            $demoStudent->classes()->attach($class->id);
        }

    }
}
