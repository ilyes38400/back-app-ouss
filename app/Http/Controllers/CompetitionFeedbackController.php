<?php

namespace App\Http\Controllers;

use App\Models\CompetitionFeedback;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompetitionFeedbackController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'competition_name' => 'required|string|max:255',
            'competition_date' => 'required|date|before_or_equal:today',
            'situation_response' => 'required|integer|in:1,2',
            'victory_response' => 'required|integer|in:1,2',
            // Attention
            'full_mindfulness' => 'required|numeric|min:0|max:10',
            'objective_clarity' => 'required|numeric|min:0|max:10',
            'letting_go' => 'required|numeric|min:0|max:10',
            'decision_relevance' => 'required|numeric|min:0|max:10',
            // Engagement
            'activation' => 'required|numeric|min:0|max:10',
            'engagement' => 'required|numeric|min:0|max:10',
            'initiative' => 'required|numeric|min:0|max:10',
            // Ressentis
            'physical_sensations' => 'required|numeric|min:0|max:10',
            'stress_tension' => 'required|numeric|min:0|max:10',
            'flow_confidence' => 'required|numeric|min:0|max:10',
            'emotional_management' => 'required|numeric|min:0|max:10',
            // Performance
            'performance_satisfaction' => 'required|numeric|min:0|max:10',
            'max_level_rating' => 'required|numeric|min:0|max:10',
            'performance_comment' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $feedback = CompetitionFeedback::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Questionnaire enregistré avec succès',
            'data' => [
                'id' => $feedback->id,
                'competition_name' => $feedback->competition_name,
                'competition_date' => $feedback->competition_date->format('Y-m-d'),
                'created_at' => $feedback->created_at->toISOString(),
                'updated_at' => $feedback->updated_at->toISOString(),
                'scores' => [
                    'situation_response' => $feedback->situation_response,
                    'victory_response' => $feedback->victory_response,
                    // Attention
                    'full_mindfulness' => $feedback->full_mindfulness,
                    'objective_clarity' => $feedback->objective_clarity,
                    'letting_go' => $feedback->letting_go,
                    'decision_relevance' => $feedback->decision_relevance,
                    // Engagement
                    'activation' => $feedback->activation,
                    'engagement' => $feedback->engagement,
                    'initiative' => $feedback->initiative,
                    // Ressentis
                    'physical_sensations' => $feedback->physical_sensations,
                    'stress_tension' => $feedback->stress_tension,
                    'flow_confidence' => $feedback->flow_confidence,
                    'emotional_management' => $feedback->emotional_management,
                    // Performance
                    'performance_satisfaction' => $feedback->performance_satisfaction,
                    'max_level_rating' => $feedback->max_level_rating,
                    'performance_comment' => $feedback->performance_comment,
                ]
            ]
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $feedbacks = CompetitionFeedback::where('user_id', $request->user()->id)
            ->orderBy('competition_date', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $feedbacks->items(),
            'total' => $feedbacks->total(),
            'current_page' => $feedbacks->currentPage(),
            'last_page' => $feedbacks->lastPage(),
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $feedback = CompetitionFeedback::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => $feedback
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $feedback = CompetitionFeedback::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'competition_name' => 'required|string|max:255',
            'competition_date' => 'required|date|before_or_equal:today',
            'situation_response' => 'required|integer|in:1,2',
            'victory_response' => 'required|integer|in:1,2',
            // Attention
            'full_mindfulness' => 'required|numeric|min:0|max:10',
            'objective_clarity' => 'required|numeric|min:0|max:10',
            'letting_go' => 'required|numeric|min:0|max:10',
            'decision_relevance' => 'required|numeric|min:0|max:10',
            // Engagement
            'activation' => 'required|numeric|min:0|max:10',
            'engagement' => 'required|numeric|min:0|max:10',
            'initiative' => 'required|numeric|min:0|max:10',
            // Ressentis
            'physical_sensations' => 'required|numeric|min:0|max:10',
            'stress_tension' => 'required|numeric|min:0|max:10',
            'flow_confidence' => 'required|numeric|min:0|max:10',
            'emotional_management' => 'required|numeric|min:0|max:10',
            // Performance
            'performance_satisfaction' => 'required|numeric|min:0|max:10',
            'max_level_rating' => 'required|numeric|min:0|max:10',
            'performance_comment' => 'nullable|string',
        ]);

        $feedback->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Questionnaire modifié avec succès',
            'data' => $feedback
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $feedback = CompetitionFeedback::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Questionnaire supprimé avec succès'
        ]);
    }
}
