<?php

namespace App\Http\Controllers\API;
use App\Models\UserWorkoutLog;
use App\Http\Resources\UserWorkoutLogResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;


class UserWorkoutLogController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
    
        $logs = UserWorkoutLog::where('user_id', $userId)
            ->with('workoutType')
            ->get();
    
        return UserWorkoutLogResource::collection($logs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'workout_type_id' => 'required|integer|exists:app_workout_types,id',
            'intensity_level' => 'nullable|in:faible,modere,intense,tres_intense',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'notes' => 'nullable|string|max:1000',
            'is_manual_entry' => 'boolean'
        ]);
    
        $date = $validated['date'] ?? now()->toDateString();
        $userId = auth()->id();
    
        if (!$userId) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $updateData = [
            'workout_type_id' => $validated['workout_type_id']
        ];

        if (isset($validated['intensity_level'])) {
            $updateData['intensity_level'] = $validated['intensity_level'];
        }

        if (isset($validated['duration_minutes'])) {
            $updateData['duration_minutes'] = $validated['duration_minutes'];
        }

        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        if (isset($validated['is_manual_entry'])) {
            $updateData['is_manual_entry'] = $validated['is_manual_entry'];
        }
    
        $log = UserWorkoutLog::updateOrCreate(
            ['user_id' => $userId, 'workout_date' => $date],
            $updateData
        );
    
        return new UserWorkoutLogResource($log);
    }

    public function getWeeklyLogs(Request $request)
    {
        $userId = auth()->id();
        $date = $request->get('date', now()->toDateString());
        
        $startOfWeek = Carbon::parse($date)->startOfWeek()->toDateString();
        $endOfWeek = Carbon::parse($date)->endOfWeek()->toDateString();
        
        $logs = UserWorkoutLog::where('user_id', $userId)
            ->whereBetween('workout_date', [$startOfWeek, $endOfWeek])
            ->with('workoutType')
            ->get()
            ->keyBy(function ($item) {
                return $item->workout_date->format('Y-m-d');
            });

        $weekData = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDate = Carbon::parse($startOfWeek)->addDays($i)->toDateString();
            $weekData[$currentDate] = $logs->get($currentDate, null);
        }
        
        return response()->json([
            'week_start' => $startOfWeek,
            'week_end' => $endOfWeek,
            'logs' => $weekData
        ]);
    }

    public function storeManualEntry(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'workout_type_id' => 'nullable|integer|exists:app_workout_types,id',
            'intensity_level' => 'nullable|in:faible,modere,intense,tres_intense',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'notes' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();
        
        if (!$userId) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $updateData = [
            'is_manual_entry' => true
        ];

        if (isset($validated['workout_type_id'])) {
            $updateData['workout_type_id'] = $validated['workout_type_id'];
        }

        if (isset($validated['intensity_level'])) {
            $updateData['intensity_level'] = $validated['intensity_level'];
        }

        if (isset($validated['duration_minutes'])) {
            $updateData['duration_minutes'] = $validated['duration_minutes'];
        }

        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $log = UserWorkoutLog::updateOrCreate(
            ['user_id' => $userId, 'workout_date' => $validated['date']],
            $updateData
        );

        return new UserWorkoutLogResource($log);
    }
}