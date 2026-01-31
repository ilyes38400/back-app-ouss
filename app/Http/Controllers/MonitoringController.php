<?php

namespace App\Http\Controllers;

use App\Models\TrainingLog;
use App\Models\CompetitionFeedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Get training stats for a specific user
     */
    public function getUserTrainingStats(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:ec_customers,id'
        ]);

        $userId = $request->user_id;

        $totalTrainings = TrainingLog::where('user_id', $userId)->count();
        $productiveTrainings = TrainingLog::where('user_id', $userId)
                                         ->where('productive', true)
                                         ->count();

        $averages = TrainingLog::where('user_id', $userId)
            ->selectRaw('
                AVG(intensity) as avg_intensity,
                AVG(perceived_fatigue) as avg_perceived_fatigue,
                AVG(engagement) as avg_engagement,
                AVG(focus) as avg_focus,
                AVG(stress) as avg_stress,
                AVG(energie_jour) as avg_energie_jour
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_trainings' => $totalTrainings,
                'productive_trainings' => $productiveTrainings,
                'average_intensity' => round($averages->avg_intensity ?? 0, 2),
                'average_perceived_fatigue' => round($averages->avg_perceived_fatigue ?? 0, 2),
                'average_engagement' => round($averages->avg_engagement ?? 0, 2),
                'average_focus' => round($averages->avg_focus ?? 0, 2),
                'average_stress' => round($averages->avg_stress ?? 0, 2),
                'average_energie_jour' => round($averages->avg_energie_jour ?? 0, 2),
            ]
        ]);
    }

    /**
     * Get training discipline stats for a specific user
     */
    public function getUserDisciplineStats(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:ec_customers,id'
        ]);

        $userId = $request->user_id;

        // Trouver la discipline la plus pratiquée
        $mostPracticedDiscipline = TrainingLog::where('user_id', $userId)
            ->select('discipline')
            ->groupBy('discipline')
            ->orderByRaw('COUNT(*) DESC')
            ->first();

        $mostPracticedStats = [];
        if ($mostPracticedDiscipline) {
            $stats = TrainingLog::where('user_id', $userId)
                ->where('discipline', $mostPracticedDiscipline->discipline)
                ->selectRaw('
                    COUNT(*) as training_count,
                    AVG(CASE
                        WHEN duration LIKE "%h%" THEN
                            CAST(SUBSTRING_INDEX(duration, "h", 1) AS UNSIGNED) * 60 +
                            IFNULL(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(duration, "h", -1), "min", 1) AS UNSIGNED), 0)
                        WHEN duration LIKE "%min%" THEN
                            CAST(SUBSTRING_INDEX(duration, "min", 1) AS UNSIGNED)
                        ELSE 0
                    END) as avg_duration_minutes
                ')
                ->first();

            $mostPracticedStats = [
                'discipline' => $mostPracticedDiscipline->discipline,
                'training_count' => $stats->training_count,
                'average_duration_minutes' => round($stats->avg_duration_minutes ?? 0, 2),
            ];
        }

        // Statistiques pour "préparation physique"
        $physicalPrepStats = TrainingLog::where('user_id', $userId)
            ->where('discipline', 'LIKE', '%préparation physique%')
            ->selectRaw('
                COUNT(*) as training_count,
                AVG(CASE
                    WHEN duration LIKE "%h%" THEN
                        CAST(SUBSTRING_INDEX(duration, "h", 1) AS UNSIGNED) * 60 +
                        IFNULL(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(duration, "h", -1), "min", 1) AS UNSIGNED), 0)
                    WHEN duration LIKE "%min%" THEN
                        CAST(SUBSTRING_INDEX(duration, "min", 1) AS UNSIGNED)
                    ELSE 0
                END) as avg_duration_minutes
            ')
            ->first();

        // Statistiques pour "Visualisation"
        $visualizationStats = TrainingLog::where('user_id', $userId)
            ->where('discipline', 'LIKE', '%visualisation%')
            ->selectRaw('
                COUNT(*) as training_count,
                AVG(CASE
                    WHEN duration LIKE "%h%" THEN
                        CAST(SUBSTRING_INDEX(duration, "h", 1) AS UNSIGNED) * 60 +
                        IFNULL(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(duration, "h", -1), "min", 1) AS UNSIGNED), 0)
                    WHEN duration LIKE "%min%" THEN
                        CAST(SUBSTRING_INDEX(duration, "min", 1) AS UNSIGNED)
                    ELSE 0
                END) as avg_duration_minutes
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'most_practiced_discipline' => $mostPracticedStats,
                'preparation_physique' => [
                    'training_count' => $physicalPrepStats->training_count ?? 0,
                    'average_duration_minutes' => round($physicalPrepStats->avg_duration_minutes ?? 0, 2),
                ],
                'visualisation' => [
                    'training_count' => $visualizationStats->training_count ?? 0,
                    'average_duration_minutes' => round($visualizationStats->avg_duration_minutes ?? 0, 2),
                ]
            ]
        ]);
    }

    /**
     * Get monthly category averages for a specific user
     */
    public function getUserMonthlyCategoryAverages(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:ec_customers,id'
        ]);

        $userId = $request->user_id;

        // Récupérer l'email de l'utilisateur
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $email = $user->email;
        \Log::info('Calling monthly-category-trends API for user', ['user_id' => $userId, 'email' => $email]);

        try {
            // Appeler l'API externe
            $response = \Illuminate\Support\Facades\Http::get('https://selfperform.fr/api/monthly-category-trends', [
                'email' => $email
            ]);

            \Log::info('Monthly-category-trends API response', ['status' => $response->status(), 'data' => $response->json()]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur API monthly-category-trends: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
    }

    /**
     * Get competition feedback averages for a specific user
     */
    public function getUserCompetitionAverages(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:ec_customers,id'
        ]);

        $userId = $request->user_id;

        // Récupérer et grouper les moyennes par date de compétition
        $averages = CompetitionFeedback::where('user_id', $userId)
            ->select([
                DB::raw('DATE(competition_date) as competition_date'),
                'competition_name',
                // Attention
                DB::raw('ROUND(AVG(full_mindfulness), 2) as full_mindfulness'),
                DB::raw('ROUND(AVG(objective_clarity), 2) as objective_clarity'),
                DB::raw('ROUND(AVG(letting_go), 2) as letting_go'),
                DB::raw('ROUND(AVG(decision_relevance), 2) as decision_relevance'),
                // Engagement
                DB::raw('ROUND(AVG(activation), 2) as activation'),
                DB::raw('ROUND(AVG(engagement), 2) as engagement'),
                DB::raw('ROUND(AVG(initiative), 2) as initiative'),
                // Ressentis
                DB::raw('ROUND(AVG(physical_sensations), 2) as physical_sensations'),
                DB::raw('ROUND(AVG(stress_tension), 2) as stress_tension'),
                DB::raw('ROUND(AVG(flow_confidence), 2) as flow_confidence'),
                DB::raw('ROUND(AVG(emotional_management), 2) as emotional_management'),
                // Performance
                DB::raw('ROUND(AVG(performance_satisfaction), 2) as performance_satisfaction'),
                DB::raw('ROUND(AVG(max_level_rating), 2) as max_level_rating'),
                DB::raw('COUNT(id) as total_competitions')
            ])
            ->groupBy(DB::raw('DATE(competition_date)'), 'competition_name')
            ->orderBy('competition_date')
            ->get();

        // Formater les données pour correspondre au format demandé
        $formattedData = $averages->map(function ($item) {
            // Calculer les moyennes par catégorie
            $attentionAverage = round((
                $item->full_mindfulness +
                $item->objective_clarity +
                $item->letting_go +
                $item->decision_relevance
            ) / 4, 2);

            $engagementAverage = round((
                $item->activation +
                $item->engagement +
                $item->initiative
            ) / 3, 2);

            $ressentiAverage = round((
                $item->physical_sensations +
                $item->stress_tension +
                $item->flow_confidence +
                $item->emotional_management
            ) / 4, 2);

            $performanceAverage = round((
                $item->performance_satisfaction +
                $item->max_level_rating
            ) / 2, 2);

            return [
                'competition_date' => $item->competition_date,
                'competition_name' => $item->competition_name,
                'total_competitions' => $item->total_competitions,
                // Moyennes par catégorie pour l'affichage principal
                'Attention' => $attentionAverage,
                'Engagement' => $engagementAverage,
                'Ressentis' => $ressentiAverage,
                'Performance' => $performanceAverage,
                // Détails par question pour le filtrage avancé (libellés français exacts)
                'Pleine conscience' => $item->full_mindfulness,
                'Clarté des objectifs' => $item->objective_clarity,
                'Lâcher prise' => $item->letting_go,
                'Pertinence décisionnelle' => $item->decision_relevance,
                'Activation' => $item->activation,
                'Engagement' => $item->engagement,
                'Prise d\'initiative' => $item->initiative,
                'Sensations physiques' => $item->physical_sensations,
                'Stress - tension' => $item->stress_tension,
                'Flow avec confiance' => $item->flow_confidence,
                'Gestion des émotions' => $item->emotional_management,
                'Satisfaction de ma performance' => $item->performance_satisfaction,
                'Note par rapport à mon niveau max' => $item->max_level_rating
            ];
        });

        return response()->json($formattedData);
    }
}
