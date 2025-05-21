<?php

namespace Database\Seeders;

use App\Models\Director;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DirectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Director::create([
            'name' => 'Juan Pérez',
            'email' => 'director@escuela.com',
            'password' => Hash::make('password'),
            'profile_img' => null,
            'school_name' => 'Instituto Avanzado',
            'school_code' => 'IA123',
            'school_email' => 'info@instituto.com',
            'school_tel' => '987654321',
            'school_type' => 'Privado',
            'membership_start' => now()->subYear(),
        ]);
    }
}
