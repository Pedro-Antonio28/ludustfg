<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class TeacherProfileController extends Controller
{
    public function show(Request $request)
    {
        $teacher = auth()->user();

        $today = now()->startOfDay();
        $clases = $teacher->schoolClasses()->with('tests.questions.answers')->get();

        $result = [];

        foreach ($clases as $class) {
            $notas = [];

            foreach ($class->tests as $test) {
                $examDate = $test->exam_date ? Carbon::parse($test->exam_date)->startOfDay() : null;

                if ($examDate && $examDate->greaterThanOrEqualTo($today)) {
                    continue; // Ignorar exámenes futuros
                }

                $total = 0;
                $max = 0;

                foreach ($test->questions as $question) {
                    foreach ($question->answers as $answer) {
                        $max += $question->mark;
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
            'name' => $teacher->name,
            'profile_img' => $teacher->profile_img,
            'classes' => $result,
        ]);
    }

    public function index(Request $request)
    {
        $teacher = auth()->user();

        $tests = \App\Models\Test::with('class')
            ->whereIn('class_id', $teacher->schoolClasses->pluck('id'))
            ->orderByDesc('exam_date')
            ->get();

        return response()->json(
            $tests->map(fn($test) => [
                'id' => $test->id,
                'title' => $test->title,
                'class_name' => $test->class->name,
                'exam_date' => $test->exam_date,
            ])
        );
    }

    public function update(Request $request)
    {
        $teacher = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_img' => 'nullable|image|max:2048',
        ]);

        $teacher->name = $validated['name'];

        if ($request->hasFile('profile_img')) {
            // Guardar nueva imagen
            $path = $request->file('profile_img')->store('Profile-Images', 'public');
            $teacher->profile_img = $path;
        }

        $teacher->save();

        return response()->json([
            'name' => $teacher->name,
            'profile_img' => $teacher->profile_img,
        ]);
    }

}
