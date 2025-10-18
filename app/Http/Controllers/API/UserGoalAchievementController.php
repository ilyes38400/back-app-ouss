<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserGoalAchievement;
use App\Models\GoalChallenge;
use App\Http\Resources\UserGoalAchievementResource;
use Illuminate\Http\Request;

class UserGoalAchievementController extends Controller
{
    /**
     * Marquer un objectif comme accompli
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'goal_challenge_id' => 'required|integer|exists:goal_challenges,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();
        
        // Récupérer l'objectif pour obtenir son type
        $goalChallenge = GoalChallenge::findOrFail($validated['goal_challenge_id']);
        
        // Vérifier si l'objectif n'a pas déjà été marqué comme accompli
        $existingAchievement = UserGoalAchievement::where('user_id', $userId)
            ->where('goal_challenge_id', $validated['goal_challenge_id'])
            ->first();
            
        if ($existingAchievement) {
            return response()->json([
                'success' => false,
                'message' => 'Cet objectif a déjà été marqué comme accompli'
            ], 400);
        }

        $achievement = UserGoalAchievement::create([
            'user_id' => $userId,
            'goal_challenge_id' => $validated['goal_challenge_id'],
            'goal_type' => $goalChallenge->theme,
            'achieved_at' => now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Objectif marqué comme accompli !',
            'data' => new UserGoalAchievementResource($achievement->load('goalChallenge'))
        ]);
    }

    /**
     * Liste des objectifs accomplis par l'utilisateur
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        $achievements = UserGoalAchievement::byUser($userId)
            ->with('goalChallenge');

        // Filtres optionnels
        if ($request->has('goal_type') && in_array($request->goal_type, ['physique', 'alimentaire', 'mental'])) {
            $achievements->byGoalType($request->goal_type);
        }

        if ($request->has('period')) {
            switch ($request->period) {
                case 'this_month':
                    $achievements->thisMonth();
                    break;
                case 'this_year':
                    $achievements->thisYear();
                    break;
            }
        }

        $achievements = $achievements->orderBy('achieved_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return UserGoalAchievementResource::collection($achievements);
    }

    /**
     * Statistiques des objectifs accomplis
     */
    public function stats()
    {
        $userId = auth()->id();
        
        $stats = [
            'total_achievements' => UserGoalAchievement::byUser($userId)->count(),
            'this_month' => UserGoalAchievement::byUser($userId)->thisMonth()->count(),
            'this_year' => UserGoalAchievement::byUser($userId)->thisYear()->count(),
            'by_type' => [
                'physique' => UserGoalAchievement::byUser($userId)->byGoalType('physique')->count(),
                'alimentaire' => UserGoalAchievement::byUser($userId)->byGoalType('alimentaire')->count(),
                'mental' => UserGoalAchievement::byUser($userId)->byGoalType('mental')->count(),
            ],
            'by_type_this_month' => [
                'physique' => UserGoalAchievement::byUser($userId)->byGoalType('physique')->thisMonth()->count(),
                'alimentaire' => UserGoalAchievement::byUser($userId)->byGoalType('alimentaire')->thisMonth()->count(),
                'mental' => UserGoalAchievement::byUser($userId)->byGoalType('mental')->thisMonth()->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Supprimer un objectif accompli (en cas d'erreur)
     */
    public function destroy($id)
    {
        $userId = auth()->id();
        
        $achievement = UserGoalAchievement::where('user_id', $userId)
            ->where('id', $id)
            ->first();
            
        if (!$achievement) {
            return response()->json([
                'success' => false,
                'message' => 'Objectif accompli introuvable'
            ], 404);
        }

        $achievement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Objectif accompli supprimé'
        ]);
    }
}
