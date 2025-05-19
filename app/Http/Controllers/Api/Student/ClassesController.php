<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentClassResource;

class ClassesController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        $classes = $student->schoolClasses()->get();

        return StudentClassResource::collection($classes);

    }

    public function show($id)
    {
        $student = auth()->user();

        $class = $student->schoolClasses()
            ->where('classes.id', $id)
            ->firstOrFail();

        return new StudentClassResource($class);
    }




}
