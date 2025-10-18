<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWorkoutLog extends Model
{
    protected $fillable = [
        'user_id', 
        'workout_date', 
        'workout_type_id',
        'intensity_level',
        'duration_minutes',
        'is_manual_entry',
        'notes'
    ];
    public $timestamps = true;

    protected $casts = [
        'workout_date' => 'date',
        'workout_type_id' => 'integer',
        'duration_minutes' => 'integer',
        'is_manual_entry' => 'boolean',
    ];

    public function workoutType()
    {
        return $this->belongsTo(WorkoutType::class, 'workout_type_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
