<?php
// app/Models/HomeInformation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class HomeInformation extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'home_informations';

    protected $fillable = [
        'title',
        'video_url',
    ];

    
    /**
     * Déclare la collection « home_video » en singleFile
     */
    public function registerMediaCollections(): void
    {
        $this
          ->addMediaCollection('home_video')
          ->singleFile();
    }

    public function getVideoUrlAttribute($value)
    {
            return $this->getFirstMediaUrl('home_video') ?: null;
        return $value;
    }
}
