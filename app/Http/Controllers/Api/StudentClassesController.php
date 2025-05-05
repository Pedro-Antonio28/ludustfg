<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class StudentClassesController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        $classes = $student->classes()->get();

        return response()->json($classes);

    }



}
