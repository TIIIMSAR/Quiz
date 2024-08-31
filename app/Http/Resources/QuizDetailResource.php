<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'owner' => $this->owner->name,
            'title' => $this->title,
            'summary' => $this->summary,
            'score' => $this->score,
            'published' => $this->published,
            'url_quiz' => $this->url_quiz,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'config' => QuizConfigResource::collection($this->whenLoaded('configs')),
        ];
    }
}
