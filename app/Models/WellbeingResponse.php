<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WellbeingResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'submitted_at',
        'sleep_quality', 'energy_wakeup', 'physical_fatigue', 'body_pain',
        'global_stress', 'control_feeling', 'mental_fatigue', 'happiness',
        'food_quality', 'natural_light', 'active_recovery',
        'raw_answers',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'raw_answers' => 'array',
        'sleep_quality' => 'float',
        'energy_wakeup' => 'float',
        'physical_fatigue' => 'float',
        'body_pain' => 'float',
        'global_stress' => 'float',
        'control_feeling' => 'float',
        'mental_fatigue' => 'float',
        'happiness' => 'float',
        'food_quality' => 'float',
        'natural_light' => 'float',
        'active_recovery' => 'float',
    ];

    /**
     * Correspondance entre les IDs de questions selfperform.fr (type "mensuel",
     * questionnaire bien-être hebdomadaire) et les champs du score.
     *
     * NOTE: il n'existe pas encore de question "Exposition lumière naturelle"
     * côté selfperform — le champ natural_light reste donc null et la moyenne
     * Hygiène est calculée sur les valeurs disponibles uniquement.
     */
    public const QUESTION_MAP = [
        210 => 'physical_fatigue',   // Fatigue physique ressentie
        212 => 'body_pain',          // Douleur et tension corporelle
        214 => 'global_stress',      // Stress global
        217 => 'happiness',          // Niveau de bonheur ressenti
        219 => 'control_feeling',    // Sentiment de contrôle
        220 => 'mental_fatigue',     // Fatigue mentale
        221 => 'sleep_quality',      // Qualité du sommeil
        223 => 'food_quality',       // Qualité de l'alimentation
        224 => 'energy_wakeup',      // Énergie au réveil
        225 => 'active_recovery',    // Temps récupération active
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
