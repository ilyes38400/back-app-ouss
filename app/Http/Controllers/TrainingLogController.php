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
            'dominance' => 'required|in:mental,physique,technique,tactique,Test Match',
            'duration' => 'required|string|max:50',
            'date' => 'required|date',
            'intensity' => 'required|numeric|between:0,10',
            'perceived_fatigue' => 'required|numeric|between:0,10',
            'engagement' => 'required|numeric|between:0,10',
            'focus' => 'required|numeric|between:0,10',
            'technical_quality' => 'nullable|numeric|between:0,10',
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
                    'technical_quality' => $trainingLog->technical_quality,
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
        // per_page permet au bilan Entraînement de charger assez de séances
        // pour tracer une courbe d'évolution.
        $perPage = min((int) $request->query('per_page', 10), 200);

        $trainingLogs = TrainingLog::where('user_id', $request->user()->id)
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        // Même forme que show() : l'app lit les notes dans un objet "scores".
        // À plat, TrainingLogScores.fromJson ne trouvait rien et retombait sur
        // sa valeur par défaut (10.0) pour toutes les séances.
        $data = collect($trainingLogs->items())->map(fn (TrainingLog $log) => [
            'id' => $log->id,
            'discipline' => $log->discipline,
            'dominance' => $log->dominance,
            'duration' => $log->duration,
            'date' => $log->date,
            'created_at' => $log->created_at->toISOString(),
            'updated_at' => $log->updated_at->toISOString(),
            'scores' => [
                'intensity' => $log->intensity,
                'perceived_fatigue' => $log->perceived_fatigue,
                'engagement' => $log->engagement,
                'focus' => $log->focus,
                'technical_quality' => $log->technical_quality,
                'stress' => $log->stress,
                'energie_jour' => $log->energie_jour,
                'comment' => $log->comment,
                'productive' => $log->productive,
            ],
        ])->values();

        return response()->json([
            'data' => $data,
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
                    'technical_quality' => $trainingLog->technical_quality,
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
            'technical_quality' => 'nullable|numeric|between:0,10',
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
                    'technical_quality' => $trainingLog->technical_quality,
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

        $averages = TrainingLog::where('user_id', $user->id)
            ->selectRaw('
                AVG(intensity) as avg_intensity,
                AVG(perceived_fatigue) as avg_perceived_fatigue,
                AVG(engagement) as avg_engagement,
                AVG(focus) as avg_focus,
                AVG(stress) as avg_stress,
                AVG(energie_jour) as avg_energie_jour
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_trainings' => $totalTrainings,
                'productive_trainings' => $productiveTrainings,
                'average_intensity' => round($averages->avg_intensity ?? 0, 2),
                'average_perceived_fatigue' => round($averages->avg_perceived_fatigue ?? 0, 2),
                'average_engagement' => round($averages->avg_engagement ?? 0, 2),
                'average_focus' => round($averages->avg_focus ?? 0, 2),
                'average_stress' => round($averages->avg_stress ?? 0, 2),
                'average_energie_jour' => round($averages->avg_energie_jour ?? 0, 2),
            ]
        ]);
    }

    public function getDisciplineStats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Trouver la discipline la plus pratiquée
        $mostPracticedDiscipline = TrainingLog::where('user_id', $user->id)
            ->select('discipline')
            ->groupBy('discipline')
            ->orderByRaw('COUNT(*) DESC')
            ->first();

        $mostPracticedStats = [];
        if ($mostPracticedDiscipline) {
            $stats = TrainingLog::where('user_id', $user->id)
                ->where('discipline', $mostPracticedDiscipline->discipline)
                ->selectRaw('
                    COUNT(*) as training_count,
                    AVG(CASE
                        WHEN duration LIKE "%h%" THEN
                            CAST(SUBSTRING_INDEX(duration, "h", 1) AS UNSIGNED) * 60 +
                            IFNULL(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(duration, "h", -1), "min", 1) AS UNSIGNED), 0)
                        WHEN duration LIKE "%min%" THEN
                            CAST(SUBSTRING_INDEX(duration, "min", 1) AS UNSIGNED)
                        ELSE 0
                    END) as avg_duration_minutes
                ')
                ->first();

            $mostPracticedStats = [
                'discipline' => $mostPracticedDiscipline->discipline,
                'training_count' => $stats->training_count,
                'average_duration_minutes' => round($stats->avg_duration_minutes ?? 0, 2),
            ];
        }

        // Statistiques pour "préparation physique"
        $physicalPrepStats = TrainingLog::where('user_id', $user->id)
            ->where('discipline', 'LIKE', '%préparation physique%')
            ->selectRaw('
                COUNT(*) as training_count,
                AVG(CASE
                    WHEN duration LIKE "%h%" THEN
                        CAST(SUBSTRING_INDEX(duration, "h", 1) AS UNSIGNED) * 60 +
                        IFNULL(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(duration, "h", -1), "min", 1) AS UNSIGNED), 0)
                    WHEN duration LIKE "%min%" THEN
                        CAST(SUBSTRING_INDEX(duration, "min", 1) AS UNSIGNED)
                    ELSE 0
                END) as avg_duration_minutes
            ')
            ->first();

        // Statistiques pour "Visualisation"
        $visualizationStats = TrainingLog::where('user_id', $user->id)
            ->where('discipline', 'LIKE', '%visualisation%')
            ->selectRaw('
                COUNT(*) as training_count,
                AVG(CASE
                    WHEN duration LIKE "%h%" THEN
                        CAST(SUBSTRING_INDEX(duration, "h", 1) AS UNSIGNED) * 60 +
                        IFNULL(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(duration, "h", -1), "min", 1) AS UNSIGNED), 0)
                    WHEN duration LIKE "%min%" THEN
                        CAST(SUBSTRING_INDEX(duration, "min", 1) AS UNSIGNED)
                    ELSE 0
                END) as avg_duration_minutes
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'most_practiced_discipline' => $mostPracticedStats,
                'preparation_physique' => [
                    'training_count' => $physicalPrepStats->training_count ?? 0,
                    'average_duration_minutes' => round($physicalPrepStats->avg_duration_minutes ?? 0, 2),
                ],
                'visualisation' => [
                    'training_count' => $visualizationStats->training_count ?? 0,
                    'average_duration_minutes' => round($visualizationStats->avg_duration_minutes ?? 0, 2),
                ]
            ]
        ]);
    }
}
