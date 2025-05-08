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
            $students = Student::factory()->count(2)->create();
            $class->students()->attach($students->pluck('id')->toArray());
        }
    }
}
