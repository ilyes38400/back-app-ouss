<?php

namespace App\Http\Controllers;

use App\Models\TrainingLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrainingLogController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'discipline' => 'required|string|max:255',
            'dominance' => 'required|in:mental,physique,technique,tactique',
            'duration' => 'required|string|max:50',
            'date' => 'required|date',
            'intensity' => 'required|numeric|between:0,10',
            'perceived_fatigue' => 'required|numeric|between:0,10',
            'engagement' => 'required|numeric|between:0,10',
            'focus' => 'required|numeric|between:0,10',
            'stress' => 'required|numeric|between:0,10',
            'energie_jour' => 'required|numeric|between:0,10',
            'comment' => 'nullable|string',
            'productive' => 'required|boolean',
        ]);

        $validated['user_id'] = $request->user()->id;

        $trainingLog = TrainingLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Carnet d\'entraînement enregistré avec succès',
            'data' => [
                'id' => $trainingLog->id,
                'discipline' => $trainingLog->discipline,
                'dominance' => $trainingLog->dominance,
                'duration' => $trainingLog->duration,
                'date' => $trainingLog->date,
                'created_at' => $trainingLog->created_at->toISOString(),
                'updated_at' => $trainingLog->updated_at->toISOString(),
                'scores' => [
                    'intensity' => $trainingLog->intensity,
                    'perceived_fatigue' => $trainingLog->perceived_fatigue,
                    'engagement' => $trainingLog->engagement,
                    'focus' => $trainingLog->focus,
                    'stress' => $trainingLog->stress,
                    'energie_jour' => $trainingLog->energie_jour,
                    'comment' => $trainingLog->comment,
                    'productive' => $trainingLog->productive,
                ]
            ]
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $trainingLogs = TrainingLog::where('user_id', $request->user()->id)
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $trainingLogs->items(),
            'total' => $trainingLogs->total(),
            'current_page' => $trainingLogs->currentPage(),
            'last_page' => $trainingLogs->lastPage(),
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $trainingLog = TrainingLog::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $trainingLog->id,
                'discipline' => $trainingLog->discipline,
                'dominance' => $trainingLog->dominance,
                'duration' => $trainingLog->duration,
                'date' => $trainingLog->date,
                'created_at' => $trainingLog->created_at->toISOString(),
                'updated_at' => $trainingLog->updated_at->toISOString(),
                'scores' => [
                    'intensity' => $trainingLog->intensity,
                    'perceived_fatigue' => $trainingLog->perceived_fatigue,
                    'engagement' => $trainingLog->engagement,
                    'focus' => $trainingLog->focus,
                    'stress' => $trainingLog->stress,
                    'energie_jour' => $trainingLog->energie_jour,
                    'comment' => $trainingLog->comment,
                    'productive' => $trainingLog->productive,
                ]
            ]
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $trainingLog = TrainingLog::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'discipline' => 'required|string|max:255',
            'dominance' => 'required|in:mental,physique,technique,tactique',
            'duration' => 'required|string|max:50',
            'date' => 'required|date',
            'intensity' => 'required|numeric|between:0,10',
            'perceived_fatigue' => 'required|numeric|between:0,10',
            'engagement' => 'required|numeric|between:0,10',
            'focus' => 'required|numeric|between:0,10',
            'stress' => 'required|numeric|between:0,10',
            'energie_jour' => 'required|numeric|between:0,10',
            'comment' => 'nullable|string',
            'productive' => 'required|boolean',
        ]);

        $trainingLog->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Carnet d\'entraînement modifié avec succès',
            'data' => $trainingLog
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $trainingLog = TrainingLog::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $trainingLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Carnet d\'entraînement supprimé avec succès'
        ]);
    }

    public function getByDate(Request $request, $date): JsonResponse
    {
        $user = $request->user();

        $trainingLog = TrainingLog::where('user_id', $user->id)
                                 ->whereDate('date', $date)
                                 ->first();

        if (!$trainingLog) {
            return response()->json(['message' => 'Aucun entraînement trouvé pour cette date'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $trainingLog->id,
                'discipline' => $trainingLog->discipline,
                'dominance' => $trainingLog->dominance,
                'duration' => $trainingLog->duration,
                'date' => $trainingLog->date,
                'created_at' => $trainingLog->created_at->toISOString(),
                'updated_at' => $trainingLog->updated_at->toISOString(),
                'scores' => [
                    'intensity' => $trainingLog->intensity,
                    'perceived_fatigue' => $trainingLog->perceived_fatigue,
                    'engagement' => $trainingLog->engagement,
                    'focus' => $trainingLog->focus,
                    'stress' => $trainingLog->stress,
                    'energie_jour' => $trainingLog->energie_jour,
                    'comment' => $trainingLog->comment,
                    'productive' => $trainingLog->productive,
                ]
            ]
        ]);
    }

    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalTrainings = TrainingLog::where('user_id', $user->id)->count();
        $productiveTrainings = TrainingLog::where('user_id', $user->id)
                                         ->where('productive', true)
                                         ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_trainings' => $totalTrainings,
                'productive_trainings' => $productiveTrainings,
            ]
        ]);
    }
}
