<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'status' => $this->status_text,
            'createed_at' => $this->created_at, 
            'start_at' => $this->started_at ? Carbon::parse($this->started_at)->toDateTimeString() : 'Not Set',
            'finished_at' => $this->finished_at ? Carbon::parse($this->finished_at)->toDateTimeString() : 'Not Set',
            'config' => QuizConfigResource::collection($this->whenLoaded('configs')),
        ];
    }
}
