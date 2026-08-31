<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserCompetition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCompetitionController extends Controller
{
    /**
     * GET /api/competitions
     * ?upcoming=1 pour ne renvoyer que les compétitions à venir (ordre chronologique).
     */
    public function index(Request $request): JsonResponse
    {
        $query = UserCompetition::where('user_id', $request->user()->id);

        if ($request->boolean('upcoming')) {
            $query->whereDate('competition_date', '>=', now()->toDateString())
                ->orderBy('competition_date');
        } else {
            $query->orderByDesc('competition_date');
        }

        return response()->json([
            'data' => $query->get()->map->toApiArray()->values(),
        ]);
    }

    /** POST /api/competitions */
    public function store(Request $request): JsonResponse
    {
        $competition = UserCompetition::create(array_merge(
            $this->validated($request),
            ['user_id' => $request->user()->id]
        ));

        return response()->json(['success' => true, 'data' => $competition->toApiArray()], 201);
    }

    /** PUT /api/competitions/{id} */
    public function update(Request $request, int $id): JsonResponse
    {
        $competition = UserCompetition::where('user_id', $request->user()->id)->findOrFail($id);
        $competition->update($this->validated($request));

        return response()->json(['success' => true, 'data' => $competition->fresh()->toApiArray()]);
    }

    /** DELETE /api/competitions/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        UserCompetition::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'competition_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'focus_tags' => 'nullable|array|max:6',
            'focus_tags.*' => 'string|max:40',
            'notes' => 'nullable|string|max:2000',
        ]);
    }
}
