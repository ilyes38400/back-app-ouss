<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MentalPreparationResource extends JsonResource
{
    public function toArray($request)
    {
        // Récupère la bonne URL (upload vs externe)
        $videoUrl = $this->video_url;
        if ($this->video_type === 'upload_video') {
            $videoUrl = $this->getFirstMediaUrl('mental_video') ?: null;
        }

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'exercise_image'=> getSingleMedia($this, 'mental_image', null),
            'video_type'  => $this->video_type,
            'video_url'   => $videoUrl,
            'status'      => $this->status,
            'program_type' => $this->program_type,
            'price'       => $this->price,
            // Nouveaux champs d'accès (seront ajoutés dynamiquement par le contrôleur)
            'user_has_access' => $this->user_has_access ?? ($this->program_type === 'free'),
            'access_reason' => $this->access_reason ?? ($this->program_type === 'free' ? 'free_program' : 'no_access'),
            'requires_purchase' => $this->requires_purchase ?? ($this->program_type === 'paid'),
            'requires_subscription' => $this->requires_subscription ?? ($this->program_type === 'premium'),
        ];
    }
}
