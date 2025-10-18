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
        'difficulty_level', 'motivation', 'focus', 'negative_focus',
        'mental_presence', 'physical_sensations', 'emotional_stability',
        'stress_tension', 'decision_making', 'competition_entry',
        'maximum_effort', 'automaticity', 'ideal_self_rating',
        'clear_objective', 'performance_comment'
    ];

    protected $casts = [
        'competition_date' => 'date',
        'difficulty_level' => 'decimal:1',
        'motivation' => 'decimal:1',
        'focus' => 'decimal:1',
        'negative_focus' => 'decimal:1',
        'mental_presence' => 'decimal:1',
        'physical_sensations' => 'decimal:1',
        'emotional_stability' => 'decimal:1',
        'stress_tension' => 'decimal:1',
        'decision_making' => 'decimal:1',
        'competition_entry' => 'decimal:1',
        'maximum_effort' => 'decimal:1',
        'automaticity' => 'decimal:1',
        'ideal_self_rating' => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
