<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentClassResource;

class ClassesController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();

        $classes = $teacher->schoolClasses()->get();

        return StudentClassResource::collection($classes);
    }
}
