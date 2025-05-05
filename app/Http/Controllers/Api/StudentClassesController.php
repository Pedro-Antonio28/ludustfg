<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentClassResource;

class StudentClassesController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        $classes = $student->classes()->get();

        return StudentClassResource::collection($classes);

    }
}
