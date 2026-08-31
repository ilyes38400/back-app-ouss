<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyTaskCompletion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'task_key', 'week_start', 'is_done'];

    protected $casts = [
        'week_start' => 'date',
        'is_done' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
