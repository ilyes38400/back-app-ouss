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
                DB::raw('ROUND(AVG(motivation), 2) as motivation'),
                DB::raw('ROUND(AVG(focus), 2) as focus'),
                DB::raw('ROUND(AVG(mental_presence), 2) as mental_presence'),
                DB::raw('ROUND(AVG(physical_sensations), 2) as physical_sensations'),
                DB::raw('ROUND(AVG(emotional_stability), 2) as emotional_stability'),
                DB::raw('ROUND(AVG(decision_making), 2) as decision_making'),
                DB::raw('ROUND(AVG(maximum_effort), 2) as maximum_effort'),
                DB::raw('ROUND(AVG(automaticity), 2) as automaticity'),
                DB::raw('ROUND(AVG(ideal_self_rating), 2) as ideal_self_rating'),
                DB::raw('ROUND(AVG(stress_tension), 2) as stress_tension'),
                DB::raw('ROUND(AVG(competition_entry), 2) as competition_entry'),
                DB::raw('COUNT(id) as total_competitions')
            ])
            ->groupBy(DB::raw('DATE(competition_date)'), 'competition_name')
            ->orderBy('competition_date')
            ->get();

        // Formater les données pour correspondre au format demandé
        $formattedData = $averages->map(function ($item) {
            return [
                'competition_date' => $item->competition_date,
                'competition_name' => $item->competition_name,
                'total_competitions' => $item->total_competitions,
                'Motivation' => $item->motivation,
                'Focus' => $item->focus,
                'Présence mentale' => $item->mental_presence,
                'Sensations physiques' => $item->physical_sensations,
                'Stabilité émotionnelle' => $item->emotional_stability,
                'Prise de décision' => $item->decision_making,
                'Effort maximum' => $item->maximum_effort,
                'Automaticité' => $item->automaticity,
                'Moi idéal' => $item->ideal_self_rating,
                'Stress-Tension' => $item->stress_tension,
                'Entrée compétition' => $item->competition_entry
            ];
        });

        return response()->json($formattedData);
    }
}