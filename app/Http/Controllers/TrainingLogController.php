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
            'intensity' => 'required|numeric|between:0,10',
            'ifp' => 'required|numeric|between:0,10',
            'engagement' => 'required|numeric|between:0,10',
            'focus' => 'required|numeric|between:0,10',
            'stress' => 'required|numeric|between:0,10',
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
                'created_at' => $trainingLog->created_at->toISOString(),
                'updated_at' => $trainingLog->updated_at->toISOString(),
                'scores' => [
                    'intensity' => $trainingLog->intensity,
                    'ifp' => $trainingLog->ifp,
                    'engagement' => $trainingLog->engagement,
                    'focus' => $trainingLog->focus,
                    'stress' => $trainingLog->stress,
                    'comment' => $trainingLog->comment,
                    'productive' => $trainingLog->productive,
                ]
            ]
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $trainingLogs = TrainingLog::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
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
            'data' => $trainingLog
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
            'intensity' => 'required|numeric|between:0,10',
            'ifp' => 'required|numeric|between:0,10',
            'engagement' => 'required|numeric|between:0,10',
            'focus' => 'required|numeric|between:0,10',
            'stress' => 'required|numeric|between:0,10',
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
}
