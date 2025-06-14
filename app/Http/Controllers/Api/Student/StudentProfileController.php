<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StudentProfileController extends Controller
{
public function show(Request $request)
{
$student = auth()->user();
$today = now()->startOfDay();

$classes = $student->classes()->with(['tests.questions' => function ($q) use ($student) {
$q->with(['answers' => fn ($a) => $a->where('student_id', $student->id)]);
}])->get();

$result = [];

foreach ($classes as $class) {
$notas = [];

foreach ($class->tests as $test) {
$examDate = $test->exam_date ? Carbon::parse($test->exam_date)->startOfDay() : null;

if ($examDate && $examDate->greaterThanOrEqualTo($today)) {
continue; // ignorar exámenes futuros
}

$total = 0;
$max = 0;

foreach ($test->questions as $question) {
$max += $question->mark;
foreach ($question->answers as $answer) {
if ($question->isCorrectAnswer($answer->answer)) {
$total += $question->mark;
}
}
}

$nota = $max > 0 ? round(($total / $max) * 10, 2) : 0;
$notas[] = $nota;
}

$classAverage = count($notas) > 0 ? round(array_sum($notas) / count($notas), 2) : null;

$result[] = [
'id' => $class->id,
'name' => $class->name,
'average_mark' => $classAverage,
];
}

return response()->json([
'name' => $student->name,
'profile_img' => $student->profile_img,
'classes' => $result,
]);
}

    public function update(Request $request)
    {
        $student = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_img' => 'nullable|image|max:2048',
        ]);

        $student->name = $validated['name'];

        if ($request->hasFile('profile_img')) {
            // Guardar nueva imagen
            $path = $request->file('profile_img')->store('Profile-Images', 'public');
            $student->profile_img = $path;
        }

        $student->save();

        return response()->json([
            'name' => $student->name,
            'profile_img' => $student->profile_img,
        ]);
    }

}

