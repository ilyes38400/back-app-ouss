<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionFeedback extends Model
{
    use HasFactory;

    protected $table = 'competition_feedbacks';

    protected $fillable = [
        'user_id', 'competition_name', 'competition_date',
        'situation_response', 'victory_response',
        // Attention
        'full_mindfulness', 'objective_clarity', 'letting_go', 'decision_relevance',
        // Engagement
        'activation', 'engagement', 'initiative',
        // Ressentis (garder physical_sensations et stress_tension)
        'physical_sensations', 'stress_tension', 'flow_confidence', 'emotional_management',
        // Performance
        'performance_satisfaction', 'max_level_rating',
        'performance_comment'
    ];

    protected $casts = [
        'competition_date' => 'date',
        // Attention
        'full_mindfulness' => 'decimal:1',
        'objective_clarity' => 'decimal:1',
        'letting_go' => 'decimal:1',
        'decision_relevance' => 'decimal:1',
        // Engagement
        'activation' => 'decimal:1',
        'engagement' => 'decimal:1',
        'initiative' => 'decimal:1',
        // Ressentis
        'physical_sensations' => 'decimal:1',
        'stress_tension' => 'decimal:1',
        'flow_confidence' => 'decimal:1',
        'emotional_management' => 'decimal:1',
        // Performance
        'performance_satisfaction' => 'decimal:1',
        'max_level_rating' => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryAverages(): array
    {
        return [
            'Attention' => round((
                $this->full_mindfulness +
                $this->objective_clarity +
                $this->letting_go +
                $this->decision_relevance
            ) / 4, 2),
            'Engagement' => round((
                $this->activation +
                $this->engagement +
                $this->initiative
            ) / 3, 2),
            'Ressentis' => round((
                $this->physical_sensations +
                $this->stress_tension +
                $this->flow_confidence +
                $this->emotional_management
            ) / 4, 2),
            'Performance' => round((
                $this->performance_satisfaction +
                $this->max_level_rating
            ) / 2, 2),
        ];
    }
}
