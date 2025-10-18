<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GoalChallenge extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
      'theme','title','description','valid_from','valid_until','status'
    ];
}
