<?php
// app/Http/Resources/HomeInformationResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeInformationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'video_url' => $this->getFirstMediaUrl('home_video') ?: null,
        ];
    }
}
