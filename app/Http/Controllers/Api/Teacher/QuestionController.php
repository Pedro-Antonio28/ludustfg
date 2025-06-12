<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function indexBank()
    {
        $questions = Question::whereNull('test_id')
            ->with('tags')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return QuestionResource::collection($questions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:single,multiple,text,match,fill_blank,fill_multiple',
            'answer' => [
                'required_if:type,single,multiple,match,fill_blank,fill_multiple',
                'array',
            ],
            'tags' => 'array',
            'tags.*' => 'string|max:50',
        ]);

        $type = $validated['type'];
        $answer = $validated['answer'] ?? null;

        switch ($type) {
            case 'single':
                if (
                    !isset($answer['correct'], $answer['options']) ||
                    count($answer) !== 2 ||
                    !is_int($answer['correct']) ||
                    !is_array($answer['options']) ||
                    count($answer['options']) < 2
                ) {
                    return response()->json(['error' => 'Formato estricto inválido para opción única.'], 422);
                }
                break;

            case 'multiple':
                if (
                    !isset($answer['correct'], $answer['options']) ||
                    count($answer) !== 2 ||
                    !is_array($answer['correct']) ||
                    !is_array($answer['options']) ||
                    count($answer['options']) < 2 ||
                    !collect($answer['correct'])->every(fn($i) => is_int($i))
                ) {
                    return response()->json(['error' => 'Formato estricto inválido para selección múltiple.'], 422);
                }
                break;

            case 'text':
                $validated['answer'] = new \stdClass();
                break;

            case 'match':
                if (
                    !isset($answer['pairs']) ||
                    !is_array($answer['pairs']) ||
                    count($answer['pairs']) < 2
                ) {
                    return response()->json(['error' => 'Formato inválido para emparejar.'], 422);
                }
                break;

            case 'fill_blank':
                if (
                    !is_array($answer) ||
                    count($answer) < 1 ||
                    !collect($answer)->every(
                        fn($blank) =>
                        isset($blank['position'], $blank['blanks']) &&
                            is_array($blank['blanks']) &&
                            count($blank['blanks']) >= 1
                    )
                ) {
                    return response()->json(['error' => 'Formato inválido para rellenar huecos.'], 422);
                }

            case 'fill_multiple':
                if (
                    !is_array($answer) ||
                    count($answer) < 1
                ) {
                    return response()->json(['error' => 'Formato inválido para huecos múltiples.'], 422);
                }
                break;
        }

        $question = Question::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'teacher_id' => auth()->id(),
            'answer' => $validated['answer'],
            'mark' => null,
            'test_id' => null,
        ]);

        $tagIds = collect($validated['tags'])->map(function ($tagName) {
            return Tag::firstOrCreate([
                'name' => $tagName,
                'teacher_id' => auth()->id(),
            ])->id;
        });

        $question->tags()->sync($tagIds);

        if (request()->has('return') && request()->input('return') === 'full') {
            return response()->json($question, 201);
        }

        return response()->noContent();

    }

    public function update(Request $request, $questionId)
    {
        $question = Question::findOrFail($questionId);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:single,multiple,text,match,fill_blank,fill_multiple',
            'answer' => [
                'required_if:type,single,multiple,match,fill_blank,fill_multiple',
                'array',
            ],
            'tags' => 'array',
            'tags.*' => 'string|max:50',
        ]);

        $type = $validated['type'];
        $answer = $validated['answer'] ?? null;

        switch ($type) {
            case 'single':
                if (
                    !isset($answer['correct'], $answer['options']) ||
                    count($answer) !== 2 ||
                    !is_int($answer['correct']) ||
                    !is_array($answer['options']) ||
                    count($answer['options']) < 2
                ) {
                    return response()->json(['error' => 'Formato estricto inválido para opción única.'], 422);
                }
                break;

            case 'multiple':
                if (
                    !isset($answer['correct'], $answer['options']) ||
                    count($answer) !== 2 ||
                    !is_array($answer['correct']) ||
                    !is_array($answer['options']) ||
                    count($answer['options']) < 2 ||
                    !collect($answer['correct'])->every(fn($i) => is_int($i))
                ) {
                    return response()->json(['error' => 'Formato estricto inválido para selección múltiple.'], 422);
                }
                break;

            case 'text':
                $validated['answer'] = new \stdClass();
                break;

            case 'match':
                if (
                    !isset($answer['pairs']) ||
                    !is_array($answer['pairs']) ||
                    count($answer['pairs']) < 2
                ) {
                    return response()->json(['error' => 'Formato inválido para emparejar.'], 422);
                }
                break;

            case 'fill_blank':
                if (
                    !is_array($answer) ||
                    count($answer) < 1 ||
                    !collect($answer)->every(
                        fn($blank) =>
                        isset($blank['position'], $blank['blanks']) &&
                            is_array($blank['blanks']) &&
                            count($blank['blanks']) >= 1
                    )
                ) {
                    return response()->json(['error' => 'Formato inválido para rellenar huecos.'], 422);
                }
                break;

            case 'fill_multiple':
                if (
                    !is_array($answer) ||
                    count($answer) < 1
                ) {
                    return response()->json(['error' => 'Formato inválido para huecos múltiples.'], 422);
                }
                break;
        }

        $question->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'answer' => $validated['answer'],
        ]);

        $tagIds = collect($validated['tags'])->map(function ($tagName) {
            return Tag::firstOrCreate([
                'name' => $tagName,
                'teacher_id' => auth()->id(),
            ])->id;
        });

        $question->tags()->sync($tagIds);

        return response()->noContent();
    }

    public function destroy($questionId)
    {
        $question = Question::findOrFail($questionId);
        // Seguridad opcional: asegurarse de que el profesor solo pueda borrar sus propias preguntas
        if ($question->teacher_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $question->tags()->detach();
        $question->delete();

        return response()->noContent();
    }

    public function assignToTest(Request $request)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'test_id' => 'required|exists:tests,id',
        ]);

        $teacherId = auth()->id();

        \Log::info('Intentando asignar test a preguntas', [
            'teacher_id' => $teacherId,
            'question_ids' => $request->question_ids,
            'test_id' => $request->test_id,
        ]);

        $affected = Question::whereIn('id', $request->question_ids)
            ->where('teacher_id', $teacherId)
            ->update(['test_id' => $request->test_id]);

        \Log::info('Número de preguntas actualizadas', [
            'actualizadas' => $affected,
        ]);

        return response()->json(['message' => 'Preguntas actualizadas']);
    }


}
