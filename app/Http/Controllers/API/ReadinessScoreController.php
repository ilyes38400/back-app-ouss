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
