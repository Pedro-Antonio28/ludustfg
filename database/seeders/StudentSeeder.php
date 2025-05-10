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

            // Asocia manualmente el primer estudiante (útil para test y auth simulada)
            $firstStudent = $students->first();
            $this->command->info("Estudiante asociado a la clase '{$class->name}': {$firstStudent->email}");
        }

        // Extra: si no hay clases aún, avisar
        if ($classes->isEmpty()) {
            $this->command->warn('No hay clases. Ejecuta el seeder de clases primero.');
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
            $this->command->info("Estudiante de prueba creado: alumno@demo.com / password123");
        }

    }
}
