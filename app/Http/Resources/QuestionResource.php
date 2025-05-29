<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = [
            'id' => $this->id,
            'title' => $this->name,
            'type' => $this->type,
            'tags' => $this->tags->pluck('name')->toArray(),
            'createdAt' => $this->created_at->format('Y-m-d'),
        ];

        // Adaptar contenido según tipo
        switch ($this->type) {
            case 'single':
                $base['content'] = [
                    'options' => $this->answer['options'] ?? [],
                    'correct_option' => $this->answer['correct'] ?? 0,
                ];
                break;

            case 'multiple':
                $base['content'] = [
                    'options' => $this->answer['options'] ?? [],
                    'correct_options' => $this->answer['correct'] ?? [],
                ];
                break;

            case 'match':
                $base['content'] = [
                    'pairs' => $this->answer['pairs'] ?? [],
                ];
                break;

            case 'text':
                $base['content'] = []; // nada que mapear
                break;

            case 'fill_blank':
                $blanks = [];
                foreach ($this->answer ?? [] as $blank) {
                    $blanks["blank_" . uniqid()] = [
                        'id' => "blank_" . uniqid(),
                        'number' => $blank['position'] + 1,
                        'correctAnswer' => $blank['blanks'][0] ?? '',
                    ];
                }
                $base['content'] = $blanks;
                break;

            case 'fill_multiple':
                $blanks = [];
                foreach ($this->answer ?? [] as $blank) {
                    $blanks["blank_" . uniqid()] = [
                        'id' => "blank_" . uniqid(),
                        'number' => $blank['position'] + 1,
                        'options' => $blank['options'] ?? [],
                        'correct' => $blank['correct'] ?? 0,
                    ];
                }
                $base['content'] = $blanks;
                break;
        }

        return $base;
    }
}
