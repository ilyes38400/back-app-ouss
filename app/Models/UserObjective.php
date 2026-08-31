<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'title', 'criterium', 'is_achieved', 'period',
    ];

    protected $casts = [
        'is_achieved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'criterium' => $this->criterium,
            'is_achieved' => $this->is_achieved,
            'period' => $this->period,
        ];
    }
}
