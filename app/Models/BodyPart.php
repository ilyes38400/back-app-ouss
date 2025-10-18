<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BodyPart extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'app_body_parts';

    protected $fillable = [ 'title', 'status' ];
}
