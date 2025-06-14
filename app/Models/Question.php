<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'teacher_id',
        'test_id',
        'type',
        'answer',
        'mark',
    ];

    protected $casts = [
        'answer' => 'array',
        'mark' => 'float',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function isCorrectAnswer($studentAnswer): ?bool
    {
        if ($studentAnswer instanceof \stdClass) {
            $studentAnswer = (array) $studentAnswer;
        }
        $normalize = fn($val) => strtolower(trim((string) $val));

        switch ($this->type) {
            case 'single':
                return ($studentAnswer['selected'] ?? null) === ($this->answer['correct'] ?? null);

            case 'multiple':
                $selected = collect($studentAnswer['selected'] ?? [])->sort()->values();
                $correct = collect($this->answer['correct'] ?? [])->sort()->values();
                return $selected->all() === $correct->all();

            case 'match':
                $correctPairs = collect($this->answer['pairs'] ?? []);
                $studentPairs = collect($studentAnswer['matches'] ?? []);

                if ($correctPairs->count() !== $studentPairs->count()) return false;

                return $correctPairs->every(function ($correctPair) use ($studentPairs, $normalize) {
                    return $studentPairs->contains(function ($studentPair) use ($correctPair, $normalize) {
                        return $normalize($studentPair['left'] ?? '') === $normalize($correctPair['left'] ?? '') &&
                            $normalize($studentPair['right'] ?? '') === $normalize($correctPair['right'] ?? '');
                    });
                });

            case 'fill_blank':
                $correctBlanks = collect($this->answer)
                    ->pluck('blanks')
                    ->flatten()
                    ->map($normalize);

                $studentAnswers = collect($studentAnswer['answers'] ?? [])->map($normalize);

                if ($correctBlanks->count() !== $studentAnswers->count()) return false;

                return $correctBlanks->values()->all() === $studentAnswers->values()->all();

            case 'fill_multiple':
                $correct = collect($this->answer)->map(
                    fn($b) => $normalize($b['options'][$b['correct']] ?? '')
                );
                $student = collect($studentAnswer['answers'] ?? [])->map($normalize);

                if ($correct->count() !== $student->count()) return false;

                return $correct->values()->all() === $student->values()->all();

            case 'text':
                return null; // requiere corrección manual

            default:
                return null;
        }
    }
}
