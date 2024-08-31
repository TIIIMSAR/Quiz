<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AzmmonDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner' => $this->owner->name,
            'title' => $this->title,
            'summary' => $this->summary,
            'url' => $this->url_quiz,
            'published' => $this->published ? 'Yes' : 'No',
            'score' => $this->score,
            'createed_at' => $this->created_at,
            'config' => QuizConfigResource::collection($this->whenLoaded('configs')),
        ];
    }
}
