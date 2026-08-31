<?php

namespace App\Services;

use App\Models\TrainingLog;
use App\Models\WellbeingResponse;
use Carbon\Carbon;

/**
 * Score "Prêt à performer" — indicateur composite 0-100 recalculé à la volée.
 *
 * Pondération : Récupération 30 + Mental 30 + Momentum 25 + Hygiène 15.
 * Les données proviennent du questionnaire bien-être hebdomadaire (miroir local
 * dans wellbeing_responses) et du carnet d'entraînement (training_logs).
 */
class ReadinessScoreService
{
    public const MAX_RECOVERY = 30;
    public const MAX_MENTAL   = 30;
    public const MAX_MOMENTUM = 25;
    public const MAX_HYGIENE  = 15;

    /** Au-delà, on considère que le questionnaire est trop ancien pour être exploitable. */
    private const STALE_DAYS = 12;
    /** À partir de ce délai on applique une décote de 10 % par jour de retard. */
    private const FRESH_DAYS = 7;
    /** Sans séance depuis ce délai, la composante Momentum tombe à 0. */
    private const MOMENTUM_STALE_DAYS = 10;

    public function calculate(int $userId): array
    {
        $wb = WellbeingResponse::where('user_id', $userId)
            ->orderByDesc('submitted_at')
            ->first();

        if (!$wb) {
            return $this->insufficient('Données insuffisantes — remplis le questionnaire bien-être');
        }

        $daysSince = (int) $wb->submitted_at->copy()->startOfDay()->diffInDays(now()->startOfDay());

        if ($daysSince > self::STALE_DAYS) {
            return $this->insufficient('Ton dernier questionnaire bien-être date de plus de 12 jours');
        }

        $decote = 1.0;
        if ($daysSince > self::FRESH_DAYS) {
            $decote = max(0.0, 1.0 - ($daysSince - self::FRESH_DAYS) * 0.10);
        }

        $recovery = $this->component([
            $wb->sleep_quality,
            $wb->energy_wakeup,
            $this->invert($wb->physical_fatigue),
            $this->invert($wb->body_pain),
        ], self::MAX_RECOVERY, $decote);

        $mental = $this->component([
            $this->invert($wb->global_stress),
            $wb->control_feeling,
            $this->invert($wb->mental_fatigue),
            $wb->happiness,
        ], self::MAX_MENTAL, $decote);

        // natural_light peut être null (pas de question correspondante côté selfperform)
        $hygiene = $this->component([
            $wb->food_quality,
            $wb->natural_light,
            $wb->active_recovery,
        ], self::MAX_HYGIENE, $decote);

        $momentum = $this->momentum($userId);

        $score = $recovery + $mental + $momentum + $hygiene;
        $score = max(0, min(100, $score));

        return [
            'has_sufficient_data' => true,
            'score' => (int) round($score),
            'zone' => $this->zone($score),
            'label' => $this->zoneLabel($score),
            'message' => $this->zoneMessage($score),
            'color' => $this->zoneColor($score),
            'components' => [
                'recovery' => ['points' => round($recovery, 1), 'max' => self::MAX_RECOVERY],
                'mental'   => ['points' => round($mental, 1),   'max' => self::MAX_MENTAL],
                'momentum' => ['points' => round($momentum, 1), 'max' => self::MAX_MOMENTUM],
                'hygiene'  => ['points' => round($hygiene, 1),  'max' => self::MAX_HYGIENE],
            ],
            'wellbeing_days_ago' => $daysSince,
            'decote' => round($decote, 2),
            'calculated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Moyenne des valeurs disponibles (les null sont ignorés) ramenée sur $max points.
     */
    private function component(array $values, float $max, float $decote): float
    {
        $available = array_values(array_filter($values, fn ($v) => $v !== null));
        if (empty($available)) {
            return 0.0;
        }

        $avg = array_sum($available) / count($available);

        return ($avg / 10) * $max * $decote;
    }

    private function invert(?float $value): ?float
    {
        return $value === null ? null : 10 - $value;
    }

    /**
     * Momentum : 3 dernières séances du carnet d'entraînement.
     * Engagement 40 % / Qualité technique 35 % / Ratio de séances productives 25 %.
     */
    private function momentum(int $userId): float
    {
        $sessions = TrainingLog::where('user_id', $userId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        if ($sessions->isEmpty()) {
            return 0.0;
        }

        $lastDate = Carbon::parse($sessions->first()->date)->startOfDay();
        if ($lastDate->diffInDays(now()->startOfDay(), false) > self::MOMENTUM_STALE_DAYS) {
            return 0.0;
        }

        $avgEngagement = (float) $sessions->avg('engagement');

        // technical_quality n'est renseigné que depuis l'ajout de la colonne :
        // on retombe sur focus pour les séances plus anciennes.
        $avgQuality = (float) $sessions->avg(
            fn ($s) => $s->technical_quality !== null ? (float) $s->technical_quality : (float) $s->focus
        );

        $ratioProductive = $sessions->where('productive', true)->count() / $sessions->count();

        $mom = $avgEngagement * 0.40 + $avgQuality * 0.35 + ($ratioProductive * 10) * 0.25;

        return ($mom / 10) * self::MAX_MOMENTUM;
    }

    private function zone(float $score): string
    {
        if ($score >= 75) return 'green';
        if ($score >= 50) return 'orange';
        return 'red';
    }

    private function zoneLabel(float $score): string
    {
        if ($score >= 75) return 'Prêt à performer';
        if ($score >= 50) return 'Performance correcte';
        return 'Vigilance';
    }

    private function zoneMessage(float $score): string
    {
        if ($score >= 75) return 'Tu es dans ta fenêtre optimale';
        if ($score >= 50) return 'Tu peux performer — surveille ton stress';
        return 'Récupération recommandée — évite de forcer';
    }

    private function zoneColor(float $score): string
    {
        if ($score >= 75) return '#1D9E75';
        if ($score >= 50) return '#EF9F27';
        return '#E24B4A';
    }

    private function insufficient(string $message): array
    {
        return [
            'has_sufficient_data' => false,
            'score' => null,
            'zone' => 'insufficient',
            'label' => 'Données insuffisantes',
            'message' => $message,
            'color' => '#888888',
            'components' => null,
            'wellbeing_days_ago' => null,
            'decote' => null,
            'calculated_at' => now()->toIso8601String(),
        ];
    }
}
