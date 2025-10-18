<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGoalAchievementResource extends JsonResource
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
            'goal_type' => $this->goal_type,
            'achieved_at' => $this->achieved_at->toDateString(),
            'notes' => $this->notes,
            'goal_challenge' => $this->when($this->goalChallenge, [
                'id' => $this->goalChallenge?->id,
                'title' => $this->goalChallenge?->title,
                'description' => $this->goalChallenge?->description,
                'theme' => $this->goalChallenge?->theme,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
