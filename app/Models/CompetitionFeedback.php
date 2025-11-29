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
        'motivation', 'focus',
        'mental_presence', 'physical_sensations', 'emotional_stability',
        'decision_making',
        'maximum_effort', 'automaticity', 'ideal_self_rating',
        'stress_tension', 'competition_entry',
        'clear_objective', 'performance_comment'
    ];

    protected $casts = [
        'competition_date' => 'date',
        'motivation' => 'decimal:1',
        'focus' => 'decimal:1',
        'mental_presence' => 'decimal:1',
        'physical_sensations' => 'decimal:1',
        'emotional_stability' => 'decimal:1',
        'decision_making' => 'decimal:1',
        'maximum_effort' => 'decimal:1',
        'automaticity' => 'decimal:1',
        'ideal_self_rating' => 'decimal:1',
        'stress_tension' => 'decimal:1',
        'competition_entry' => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
