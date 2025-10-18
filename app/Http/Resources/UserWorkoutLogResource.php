<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserWorkoutLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->workout_date->toDateString(),
            'workout_type_id' => $this->workout_type_id,
            'intensity_level' => $this->intensity_level,
            'duration_minutes' => $this->duration_minutes,
            'is_manual_entry' => $this->is_manual_entry,
            'notes' => $this->notes,
            'workout_type' => $this->when($this->workoutType, [
                'id' => $this->workoutType?->id,
                'title' => $this->workoutType?->title,
            ]),
        ];
    }
}
