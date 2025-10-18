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
            'difficulty_level' => 'required|numeric|between:0,10',
            'motivation' => 'required|numeric|between:0,10',
            'focus' => 'required|numeric|between:0,10',
            'negative_focus' => 'required|numeric|between:0,10',
            'mental_presence' => 'required|numeric|between:0,10',
            'physical_sensations' => 'required|numeric|between:0,10',
            'emotional_stability' => 'required|numeric|between:0,10',
            'stress_tension' => 'required|numeric|between:0,10',
            'decision_making' => 'required|numeric|between:0,10',
            'competition_entry' => 'required|numeric|between:0,10',
            'maximum_effort' => 'required|numeric|between:0,10',
            'automaticity' => 'required|numeric|between:0,10',
            'ideal_self_rating' => 'required|numeric|between:0,10',
            'clear_objective' => 'required|string',
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
                    'difficulty_level' => $feedback->difficulty_level,
                    'motivation' => $feedback->motivation,
                    'focus' => $feedback->focus,
                    'negative_focus' => $feedback->negative_focus,
                    'mental_presence' => $feedback->mental_presence,
                    'physical_sensations' => $feedback->physical_sensations,
                    'emotional_stability' => $feedback->emotional_stability,
                    'stress_tension' => $feedback->stress_tension,
                    'decision_making' => $feedback->decision_making,
                    'competition_entry' => $feedback->competition_entry,
                    'maximum_effort' => $feedback->maximum_effort,
                    'automaticity' => $feedback->automaticity,
                    'ideal_self_rating' => $feedback->ideal_self_rating,
                    'clear_objective' => $feedback->clear_objective,
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
            'difficulty_level' => 'required|numeric|between:0,10',
            'motivation' => 'required|numeric|between:0,10',
            'focus' => 'required|numeric|between:0,10',
            'negative_focus' => 'required|numeric|between:0,10',
            'mental_presence' => 'required|numeric|between:0,10',
            'physical_sensations' => 'required|numeric|between:0,10',
            'emotional_stability' => 'required|numeric|between:0,10',
            'stress_tension' => 'required|numeric|between:0,10',
            'decision_making' => 'required|numeric|between:0,10',
            'competition_entry' => 'required|numeric|between:0,10',
            'maximum_effort' => 'required|numeric|between:0,10',
            'automaticity' => 'required|numeric|between:0,10',
            'ideal_self_rating' => 'required|numeric|between:0,10',
            'clear_objective' => 'required|string',
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
