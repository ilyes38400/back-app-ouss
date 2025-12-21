<?php

namespace App\Http\Controllers;

use App\Models\CompetitionFeedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuestionnaireApiController extends Controller
{
    public function getCompetitionFeedbackAverages(Request $request): JsonResponse
    {
        // Validation du paramètre email
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->query('email');

        // Récupérer l'utilisateur via email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Participant non trouvé'
            ], 404);
        }

        // Récupérer et grouper les moyennes par date de compétition
        $averages = CompetitionFeedback::where('user_id', $user->id)
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