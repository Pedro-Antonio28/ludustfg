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
        switch ($this->type) {
            case 'single':
                return ($studentAnswer['selected'] ?? null) === ($this->answer['correct'] ?? null);

            case 'multiple':
                $selected = collect($studentAnswer['selected'] ?? []);
                $correct = collect($this->answer['correct'] ?? []);
                return $selected->sort()->values()->all() === $correct->sort()->values()->all();

            case 'match':
                $correctPairs = collect($this->answer['pairs'] ?? []);
                $studentPairs = collect($studentAnswer['matches'] ?? []);
                return $correctPairs->count() > 0 &&
                    $correctPairs->values()->all() === $studentPairs->values()->all();

            case 'fill_blank':
                $correct = collect($this->answer)->pluck('blanks')->flatten()->map('strtolower');
                $student = collect($studentAnswer['answers'] ?? [])->map('strtolower');

                return $correct->count() > 0 &&
                    $correct->values()->all() === $student->values()->all();

            case 'fill_multiple':
                $correct = collect($this->answer)->map(
                    fn($b) => strtolower($b['options'][$b['correct']])
                );
                $student = collect($studentAnswer['answers'] ?? [])->map('strtolower');

                return $correct->count() > 0 &&
                    $correct->values()->all() === $student->values()->all();

            case 'text':
                return null; // revisión manual

            default:
                return null;
        }
    }
}
