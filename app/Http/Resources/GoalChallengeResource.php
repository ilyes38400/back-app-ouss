<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoalChallengeResource extends JsonResource
{
    public function toArray($req)
    {
        return [
          'id'          => $this->id,
          'theme'       => $this->theme,
          'title'       => $this->title,
          'description' => $this->description,
          'valid_from'  => $this->valid_from,
          'valid_until' => $this->valid_until
        ];
    }
}
