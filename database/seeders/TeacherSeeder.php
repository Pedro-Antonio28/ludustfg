<?php

namespace Database\Seeders;

use App\Models\Director;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $director = Director::first();

        Teacher::create([
            'name' => 'Laura Gómez',
            'email' => 'laura@escuela.com',
            'pass' => Hash::make('password'),
            'profile_img' => null,
            'director_id' => $director->id,
        ]);
    }
}
