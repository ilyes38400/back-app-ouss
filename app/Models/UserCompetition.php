<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCompetition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'competition_date', 'location', 'focus_tags', 'notes',
    ];

    protected $casts = [
        'competition_date' => 'date',
        'focus_tags' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toApiArray(): array
    {
        $date = $this->competition_date;
        $daysLeft = (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'competition_date' => $date->format('Y-m-d'),
            'location' => $this->location,
            'focus_tags' => $this->focus_tags ?? [],
            'notes' => $this->notes,
            'days_left' => $daysLeft,
        ];
    }
}
