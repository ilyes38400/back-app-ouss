<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AnnualQuestionnaireSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Campagne annuelle du questionnaire de départ.
 *
 * Il se repasse chaque année à partir du 1er septembre. Quelqu'un qui a
 * répondu en février relève de la campagne de septembre précédente : il peut
 * donc le refaire dès le septembre suivant.
 */
class AnnualQuestionnaireController extends Controller
{
    /** GET /api/annual-questionnaire/status */
    public function status(Request $request): JsonResponse
    {
        $periodStart = AnnualQuestionnaireSubmission::currentPeriodStart();

        $submission = AnnualQuestionnaireSubmission::where('user_id', $request->user()->id)
            ->whereDate('period_start', $periodStart->toDateString())
            ->orderByDesc('submitted_at')
            ->first();

        $lastEver = AnnualQuestionnaireSubmission::where('user_id', $request->user()->id)
            ->orderByDesc('submitted_at')
            ->first();

        return response()->json([
            // Répondu pour la campagne en cours ?
            'completed' => $submission !== null,
            // Donc proposable tant que ce n'est pas fait.
            'available' => $submission === null,
            'submitted_at' => $submission?->submitted_at->toIso8601String(),
            'last_submitted_at' => $lastEver?->submitted_at->toIso8601String(),
            'period_start' => $periodStart->toDateString(),
            'period_label' => 'Septembre ' . $periodStart->year,
            'next_period_start' => $periodStart->copy()->addYear()->toDateString(),
        ]);
    }

    /**
     * POST /api/annual-questionnaire/submissions
     *
     * Enregistre le passage. Les réponses restent sur selfperform.fr ;
     * on ne garde ici que la date, pour piloter la campagne.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'submitted_at' => 'nullable|date',
        ]);

        $submittedAt = isset($validated['submitted_at'])
            ? \Carbon\Carbon::parse($validated['submitted_at'])
            : now();

        $periodStart = AnnualQuestionnaireSubmission::currentPeriodStart($submittedAt);

        $submission = AnnualQuestionnaireSubmission::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'period_start' => $periodStart->toDateString(),
            ],
            ['submitted_at' => $submittedAt]
        );

        return response()->json([
            'success' => true,
            'id' => $submission->id,
            'period_start' => $periodStart->toDateString(),
        ], 201);
    }
}
