<?php

namespace App\Http\Resources;

use App\Models\Take;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserQuizResource extends JsonResource
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
            'quiz_title' => $this->quiz->title,
            'score' => $this->score,
        ];
    }
}
