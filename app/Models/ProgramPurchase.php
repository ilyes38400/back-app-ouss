<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramPurchase extends Model
{
    use HasFactory;

    protected $table = 'program_purchases';

    protected $fillable = [
        'user_id',
        'program_id',
        'program_type', // 'workout' ou 'mental'
        'program_title',
        'program_data',
        'purchase_platform', // 'apple', 'google', 'stripe', etc.
        'platform_transaction_id',
        'platform_product_id',
        'receipt_data',
        'purchase_date',
        'price',
        'currency',
        'status', // 'active', 'expired', 'refunded'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'program_id' => 'integer',
        'program_data' => 'array',
        'receipt_data' => 'array',
        'purchase_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class, 'program_id', 'id');
    }

    public function mentalPreparation()
    {
        return $this->belongsTo(MentalPreparation::class, 'program_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('program_type', $type);
    }

    public function scopeByProgram($query, $programId, $programType)
    {
        return $query->where('program_id', $programId)
                    ->where('program_type', $programType);
    }

    public function isValid()
    {
        return $this->status === 'active';
    }

    public static function hasUserPurchased($userId, $programId, $programType)
    {
        return self::active()
                   ->forUser($userId)
                   ->byProgram($programId, $programType)
                   ->exists();
    }

    /**
     * Nettoie les achats pour des programmes qui n'existent plus
     */
    public static function cleanOrphanedPurchases()
    {
        // Nettoyer les achats de workouts supprimés
        $orphanedWorkouts = self::where('program_type', 'workout')
            ->whereNotExists(function ($query) {
                $query->select('id')
                      ->from('workouts')
                      ->whereColumn('workouts.id', 'program_purchases.program_id')
                      ->where('workouts.status', 'active');
            })
            ->update(['status' => 'expired']);

        // Nettoyer les achats de mental preparations supprimées
        $orphanedMental = self::where('program_type', 'mental')
            ->whereNotExists(function ($query) {
                $query->select('id')
                      ->from('mental_preparations')
                      ->whereColumn('mental_preparations.id', 'program_purchases.program_id')
                      ->where('mental_preparations.status', 'active');
            })
            ->update(['status' => 'expired']);

        return ['workouts' => $orphanedWorkouts, 'mental' => $orphanedMental];
    }
}