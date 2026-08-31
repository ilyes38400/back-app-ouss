<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WellbeingResponse;
use App\Services\ReadinessScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadinessScoreController extends Controller
{
    public function __construct(private ReadinessScoreService $service)
    {
    }

    /**
     * GET /api/wellbeing-responses/status
     *
     * Permet à l'app de savoir si le questionnaire de la semaine est déjà
     * rempli, avant même d'ouvrir le formulaire.
     */
    public function weeklyStatus(Request $request): JsonResponse
    {
        $existing = $this->currentWeekResponse($request->user()->id);
        $weekStart = now()->startOfWeek();

        return response()->json([
            'completed' => $existing !== null,
            'submitted_at' => $existing?->submitted_at->toIso8601String(),
            'week_start' => $weekStart->toDateString(),
            'week_end' => now()->endOfWeek()->toDateString(),
            'next_available_at' => $weekStart->copy()->addWeek()->toIso8601String(),
        ]);
    }

    private function currentWeekResponse(int $userId): ?WellbeingResponse
    {
        return WellbeingResponse::where('user_id', $userId)
            ->whereBetween('submitted_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderByDesc('submitted_at')
            ->first();
    }

    /** GET /api/readiness-score */
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->service->calculate($request->user()->id));
    }

    /**
     * POST /api/wellbeing-responses
     *
     * Miroir local d'une soumission du questionnaire bien-être hebdomadaire.
     * L'app envoie le même payload que celui posté à selfperform.fr :
     *   { "responses": { "<question_id>": {"score": 7, "category_id": 52}, ... } }
     * On ne conserve que les questions utiles au score, plus le brut.
     */
    public function storeWellbeing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'responses' => 'required|array',
            'submitted_at' => 'nullable|date',
        ]);

        // Le questionnaire bien-être est hebdomadaire : une soumission par
        // semaine calendaire, sinon les moyennes et le score sont faussés.
        $existing = $this->currentWeekResponse($request->user()->id);
        if ($existing) {
            return response()->json([
                'message' => 'Questionnaire déjà rempli cette semaine',
                'submitted_at' => $existing->submitted_at->toIso8601String(),
                'next_available_at' => now()->startOfWeek()->addWeek()->toIso8601String(),
            ], 409);
        }

        $raw = [];
        $fields = [];

        foreach ($validated['responses'] as $questionId => $answer) {
            $score = is_array($answer) ? ($answer['score'] ?? null) : $answer;
            if ($score === null || !is_numeric($score)) {
                continue;
            }

            $questionId = (int) $questionId;
            $score = (float) $score;
            $raw[$questionId] = $score;

            if (isset(WellbeingResponse::QUESTION_MAP[$questionId])) {
                $fields[WellbeingResponse::QUESTION_MAP[$questionId]] = $score;
            }
        }

        if (empty($raw)) {
            return response()->json(['message' => 'Aucune réponse exploitable'], 422);
        }

        $response = WellbeingResponse::create(array_merge($fields, [
            'user_id' => $request->user()->id,
            'submitted_at' => $validated['submitted_at'] ?? now(),
            'raw_answers' => $raw,
        ]));

        return response()->json([
            'success' => true,
            'id' => $response->id,
            'readiness' => $this->service->calculate($request->user()->id),
        ], 201);
    }
}
