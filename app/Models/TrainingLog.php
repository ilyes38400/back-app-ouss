<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'discipline', 'dominance', 'duration', 'date',
        'intensity', 'perceived_fatigue', 'engagement', 'focus', 'stress',
        'energie_jour', 'comment', 'productive'
    ];

    protected $casts = [
        'intensity' => 'decimal:1',
        'perceived_fatigue' => 'decimal:1',
        'engagement' => 'decimal:1',
        'focus' => 'decimal:1',
        'stress' => 'decimal:1',
        'energie_jour' => 'decimal:1',
        'productive' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
