<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Exercise;

class WorkoutDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user_id = auth()->id() ?? null;
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'status'        => $this->status,
            'workout_image' => getSingleMedia($this, 'workout_image',null),
            'level_id'      => $this->level_id,
            'level_title'   => optional($this->level)->title,
            'level_rate'    => optional($this->level)->rate,
            'workout_type_id'       => $this->workout_type_id,
            'workout_type_title'    => optional($this->workouttype)->title,
            'is_monthly_program' => $this->is_monthly_program,
            'is_favourite'  => $this->userFavouriteWorkout->where('user_id',$user_id)->first() ? 1 : 0,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,

            // Nouveaux champs requis
            'program_type'  => $this->program_type ?? 'free',
            'price'         => $this->price,
            'user_has_access' => $this->user_has_access ?? true,
            'access_reason' => $this->access_reason ?? 'unknown',
            'requires_purchase' => $this->requires_purchase ?? false,
            'requires_subscription' => $this->requires_subscription ?? false,

            // Compatibilité avec l'ancien système
            'is_premium'    => $this->program_type === 'premium' ? 1 : 0,
        ];
    }
}
