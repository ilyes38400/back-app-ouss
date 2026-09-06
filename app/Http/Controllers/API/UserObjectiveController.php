<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserObjective;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserObjectiveController extends Controller
{
    /**
     * GET /api/objectives
     * ?period=YYYY-MM (défaut : mois en cours)
     */
    public function index(Request $request): JsonResponse
    {
        $period = $request->query('period', now()->format('Y-m'));

        $objectives = UserObjective::where('user_id', $request->user()->id)
            ->where('period', $period)
            ->orderBy('id')
            ->get();

        return response()->json([
            'period' => $period,
            'data' => $objectives->map->toApiArray()->values(),
        ]);
    }

    /** POST /api/objectives */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['period'] = $data['period'] ?? now()->format('Y-m');

        $objective = UserObjective::create(array_merge($data, ['user_id' => $request->user()->id]));

        return response()->json(['success' => true, 'data' => $objective->fresh()->toApiArray()], 201);
    }

    /** PUT /api/objectives/{id} */
    public function update(Request $request, int $id): JsonResponse
    {
        $objective = UserObjective::where('user_id', $request->user()->id)->findOrFail($id);
        $objective->update($this->validated($request, partial: true));

        return response()->json(['success' => true, 'data' => $objective->fresh()->toApiArray()]);
    }

    /** DELETE /api/objectives/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        UserObjective::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'type' => "$required|in:technique,tactique,physique,mental,autre",
            'title' => "$required|string|max:255",
            'criterium' => 'nullable|string|max:255',
            'is_achieved' => 'nullable|boolean',
            'period' => 'nullable|string|size:7',
        ]);
    }
}
