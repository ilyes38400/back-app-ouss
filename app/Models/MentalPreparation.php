<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MentalPreparation extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'video_type',   // 'upload_video' ou 'external_url'
        'video_url',    // URL si external_url
        'program_type', // 'free', 'premium', 'paid'
        'price',        // Prix si paid
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Déclare la collection pour l’upload de vidéo.
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('mental_video')
            ->singleFile();
    }

    /**
     * Accessor pour retourner l’URL de la vidéo uploadée
     * si video_type === 'upload_video', sinon renvoie video_url brut.
     */
    public function getVideoUrlAttribute($value)
    {
        if ($this->video_type === 'upload') {
            return $this->getFirstMediaUrl('mental_video') ?: null;
        }
        return $value;
    }
}
