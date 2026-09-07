<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnualQuestionnaireSubmission extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'submitted_at', 'period_start'];

    protected $casts = [
        'submitted_at' => 'datetime',
        'period_start' => 'date',
    ];

    /** Mois d'ouverture de la campagne annuelle. */
    public const CAMPAIGN_MONTH = 9;

    /**
     * Début de la campagne en cours : le 1er septembre le plus récent.
     *
     * Une réponse donnée en février se rattache donc au septembre précédent,
     * et l'athlète peut repasser le questionnaire dès le septembre suivant.
     */
    public static function currentPeriodStart(?Carbon $reference = null): Carbon
    {
        $reference = ($reference ?? now())->copy();

        $year = $reference->month >= self::CAMPAIGN_MONTH
            ? $reference->year
            : $reference->year - 1;

        return Carbon::create($year, self::CAMPAIGN_MONTH, 1)->startOfDay();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
