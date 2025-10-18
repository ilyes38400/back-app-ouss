<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGoalAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_challenge_id',
        'goal_type',
        'achieved_at',
        'notes'
    ];

    protected $casts = [
        'achieved_at' => 'date',
        'user_id' => 'integer',
        'goal_challenge_id' => 'integer',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function goalChallenge()
    {
        return $this->belongsTo(GoalChallenge::class, 'goal_challenge_id', 'id');
    }

    // Scopes
    public function scopeByGoalType($query, $type)
    {
        return $query->where('goal_type', $type);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('achieved_at', now()->month)
                    ->whereYear('achieved_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('achieved_at', now()->year);
    }
}
