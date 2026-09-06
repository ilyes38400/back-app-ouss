<?php

namespace App\Http\Controllers;

use App\Models\CompetitionFeedback;
use App\Models\User;
use App\Models\WellbeingResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuestionnaireApiController extends Controller
{
    /** Questions selfperform composant chaque catégorie du bien-être. */
    private const CATEGORY_QUESTION_IDS = [
        'Santé physique' => [210, 211, 212, 213],
        'Santé mentale' => [214, 215, 216, 217, 218, 219, 220],
        'Hygiène' => [221, 222, 223, 224, 225, 226],
        'Productivité' => [227, 228, 229, 230, 231, 232],
    ];

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

    public function getWeeklyCategoryDetails(Request $request): JsonResponse
    {
        $email = $request->query('email');
        if (!$email) {
            return response()->json([]);
        }

        $questionMap = [
            51 => [
                ['id' => 210, 'label' => 'Fatigue physique ressentie'],
                ['id' => 211, 'label' => 'Récupération perçue'],
                ['id' => 212, 'label' => 'Douleur et tension corporelle'],
                ['id' => 213, 'label' => 'Niveau d\'énergie'],
            ],
            52 => [
                ['id' => 214, 'label' => 'Stress global'],
                ['id' => 215, 'label' => 'Sentiment de confiance'],
                ['id' => 216, 'label' => 'Stabilité émotionnelle'],
                ['id' => 217, 'label' => 'Niveau de bonheur ressenti'],
                ['id' => 218, 'label' => 'Vie sociale active'],
                ['id' => 219, 'label' => 'Sentiment de contrôle'],
                ['id' => 220, 'label' => 'Fatigue mentale'],
            ],
            53 => [
                ['id' => 221, 'label' => 'Qualité du sommeil'],
                ['id' => 222, 'label' => 'Durée du sommeil'],
                ['id' => 223, 'label' => 'Qualité de l\'alimentation'],
                ['id' => 224, 'label' => 'Énergie au réveil'],
                ['id' => 225, 'label' => 'Temps récupération active'],
                ['id' => 226, 'label' => 'Temps d\'écran'],
            ],
            54 => [
                ['id' => 227, 'label' => 'Productivité travail'],
                ['id' => 228, 'label' => 'Entraînements de qualité'],
                ['id' => 229, 'label' => 'Gestion du temps'],
                ['id' => 230, 'label' => 'Présence dans l\'instant'],
                ['id' => 231, 'label' => 'Sensation d\'efficacité'],
                ['id' => 232, 'label' => 'Satisfaction journées'],
            ],
        ];

        $catNames = [
            51 => 'Santé physique',
            52 => 'Santé mentale',
            53 => 'Hygiène',
            54 => 'Productivité',
        ];

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([]);
        }

        // Dernière soumission réelle du questionnaire bien-être.
        // Le détail doit décrire EXACTEMENT la même chose que les moyennes et le
        // dernier point de la courbe : la semaine la plus récente, pas la
        // dernière soumission. Avec deux soumissions dans la même semaine, les
        // deux écrans affichaient des chiffres différents pour la même période.
        $latest = WellbeingResponse::where('user_id', $user->id)
            ->orderByDesc('submitted_at')
            ->first();

        if (!$latest) {
            return response()->json([]);
        }

        $weekStart = $latest->submitted_at->copy()->startOfWeek();
        $weekResponses = WellbeingResponse::where('user_id', $user->id)
            ->whereBetween('submitted_at', [$weekStart, $weekStart->copy()->endOfWeek()])
            ->get();

        // Toutes les valeurs relevées dans la semaine, question par question.
        $answersByQuestion = [];
        foreach ($weekResponses as $response) {
            foreach (($response->raw_answers ?? []) as $questionId => $score) {
                $answersByQuestion[(int) $questionId][] = (float) $score;
            }
        }

        if (empty($answersByQuestion)) {
            return response()->json([]);
        }

        $result = [];
        foreach ($questionMap as $catId => $questions) {
            $qScores = [];
            $allValues = [];

            foreach ($questions as $q) {
                // Une question sans réponse est omise plutôt qu'inventée.
                if (!isset($answersByQuestion[$q['id']])) {
                    continue;
                }

                $values = $answersByQuestion[$q['id']];
                $allValues = array_merge($allValues, $values);

                $qScores[] = [
                    'id' => $q['id'],
                    'label' => $q['label'],
                    'score' => round(array_sum($values) / count($values), 1),
                ];
            }

            if (empty($qScores)) {
                continue;
            }

            $result[] = [
                'category_id' => $catId,
                'category_name' => $catNames[$catId],
                // Même calcul que weekly-category-trends : moyenne de toutes les
                // valeurs de la catégorie, pour que les deux écrans concordent.
                'average' => round(array_sum($allValues) / count($allValues), 1),
                'questions' => $qScores,
            ];
        }

        return response()->json($result);
    }

    public function getWeeklyCategoryTrends(Request $request): JsonResponse
    {
        $email = $request->query('email');
        if (!$email) {
            return response()->json([]);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->get('https://selfperform.fr/api/weekly-category-trends', ['email' => $email]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data)) {
                    return response()->json($data);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('selfperform weekly-category-trends failed: ' . $e->getMessage());
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([]);
        }

        // Repli sur le miroir local : uniquement de vraies soumissions.
        // Un compte neuf doit renvoyer une liste vide, pas une courbe inventée.
        $responses = WellbeingResponse::where('user_id', $user->id)
            ->orderBy('submitted_at')
            ->get();

        if ($responses->isEmpty()) {
            return response()->json([]);
        }

        $weeks = $responses
            ->groupBy(fn (WellbeingResponse $r) => $r->submitted_at->copy()->startOfWeek()->format('Y-m-d'))
            ->map(function ($group, $weekStartDate) {
                $weekStart = \Carbon\Carbon::parse($weekStartDate);

                $categoryAverages = [];
                foreach (self::CATEGORY_QUESTION_IDS as $categoryName => $questionIds) {
                    $values = [];
                    foreach ($group as $response) {
                        foreach ($questionIds as $questionId) {
                            $score = ($response->raw_answers ?? [])[$questionId] ?? null;
                            if ($score !== null) {
                                $values[] = (float) $score;
                            }
                        }
                    }

                    if ($values) {
                        $categoryAverages[$categoryName] = round(array_sum($values) / count($values), 1);
                    }
                }

                return array_merge([
                    'week' => sprintf('%d-W%02d', $weekStart->year, $weekStart->weekOfYear),
                    'week_start' => $weekStart->format('Y-m-d'),
                ], $categoryAverages);
            })
            ->values();

        return response()->json($weeks);
    }
}