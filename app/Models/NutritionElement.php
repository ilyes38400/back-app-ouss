<?php

// app/Models/NutritionElement.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class NutritionElement extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['title','slug','description','status'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}
