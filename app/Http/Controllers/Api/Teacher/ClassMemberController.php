<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Models\Student;


class ClassMemberController extends Controller
{
    public function index($classId)
    {
        try {
            $schoolClass = SchoolClass::with('students')->findOrFail($classId);
            return response()->json($schoolClass->students);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function destroy($classId, $studentId)
    {
        $class = SchoolClass::findOrFail($classId);

        // Verifica que el profesor actual es dueño de la clase
        if ($class->teacher_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Desvincular estudiante de la clase
        $class->students()->detach($studentId);

        return response()->json(['message' => 'Estudiante eliminado correctamente']);
    }

}
