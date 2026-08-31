<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WeeklyTaskCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class WeeklyTaskController extends Controller
{
    /**
     * GET /api/weekly-tasks
     *
     * Tâches de la semaine affichées sur l'accueil.
     *
     * Les tâches viennent du back-office selfperform.fr (Tâches hebdomadaires).
     * Si cette API est injoignable, on retombe sur une liste par défaut pour ne
     * jamais afficher un accueil amputé.
     *
     * Deux tâches calculées automatiquement (questionnaire bien-être rempli,
     * séance enregistrée) ont été retirées à la demande : elles n'étaient pas
     * modifiables depuis le back-office. Voir l'historique git pour les
     * remettre — le code de calcul tenait en deux requêtes.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        // Cases cochées manuellement cette semaine.
        $overrides = WeeklyTaskCompletion::where('user_id', $userId)
            ->whereDate('week_start', $weekStart->toDateString())
            ->pluck('is_done', 'task_key');

        $tasks = $this->editorialTasks();

        $tasks = array_map(function (array $task) use ($overrides) {
            if ($overrides->has($task['key'])) {
                $task['done'] = (bool) $overrides[$task['key']];
            }

            $task['tag'] = $this->tagFor($task);
            $task['tag_type'] = $task['done'] ? 'done' : $task['tag_type'];

            return $task;
        }, $tasks);

        return response()->json([
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'data' => $tasks,
        ]);
    }

    /**
     * POST /api/weekly-tasks/toggle
     * { "key": "challenge", "done": true }
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:64',
            'done' => 'required|boolean',
        ]);

        WeeklyTaskCompletion::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'task_key' => $validated['key'],
                'week_start' => now()->startOfWeek()->toDateString(),
            ],
            ['is_done' => $validated['done']]
        );

        return $this->index($request);
    }

    /**
     * Tâches éditoriales publiées sur selfperform.fr, mises en cache 15 minutes
     * pour ne pas appeler le site à chaque ouverture de l'accueil.
     */
    private function editorialTasks(): array
    {
        try {
            $tasks = Cache::remember('selfperform.weekly_tasks', now()->addMinutes(15), function () {
                $response = Http::withOptions(['verify' => false])
                    ->timeout(5)
                    ->get(rtrim(config('services.selfperform.url'), '/') . '/api/weekly-tasks');

                if (! $response->successful()) {
                    throw new \RuntimeException('HTTP ' . $response->status());
                }

                $data = $response->json('data');

                // Une liste vide serait mise en cache 15 min et viderait l'accueil :
                // on préfère lever et repartir sur le repli.
                if (empty($data)) {
                    throw new \RuntimeException('Réponse vide');
                }

                return $data;
            });
        } catch (\Throwable $e) {
            \Log::warning('selfperform weekly-tasks indisponible: ' . $e->getMessage());

            return $this->fallbackEditorialTasks();
        }

        return $this->formatEditorialTasks($tasks);
    }

    private function formatEditorialTasks(array $tasks): array
    {
        return array_map(function (array $task) {
            $labelType = $task['label_type'] ?? 'neutral';

            return [
                // Préfixe "sp_" : la clé sert à retrouver la case cochée, elle
                // doit rester stable et ne pas heurter les clés calculées.
                'key' => 'sp_' . ($task['id'] ?? 0),
                'title' => $task['title'] ?? '',
                'subtitle' => $task['subtitle'] ?? '',
                'done' => false,
                'action' => $labelType === 'mental' ? 'mental_preparation' : null,
                'tag_type' => $labelType,
                'label' => $task['label'] ?? null,
            ];
        }, $tasks);
    }

    /**
     * Repli si selfperform.fr est injoignable : le contenu par défaut, identique
     * à ce que le back-office contient à l'installation.
     */
    private function fallbackEditorialTasks(): array
    {
        return $this->formatEditorialTasks([
            [
                'id' => 1,
                'title' => 'Cohérence cardiaque 5 min',
                'subtitle' => 'Challenge de la semaine',
                'label' => 'Mental',
                'label_type' => 'mental',
            ],
            [
                'id' => 2,
                'title' => 'Visualisation pré-compétition',
                'subtitle' => 'Recommandé avant ta compét',
                'label' => 'Mental',
                'label_type' => 'mental',
            ],
            [
                'id' => 3,
                'title' => 'Challenge de la semaine',
                'subtitle' => 'Chaque pensée négative → 3 pensées positives',
                'label' => 'Défi',
                'label_type' => 'challenge',
            ],
        ]);
    }

    private function tagFor(array $task): string
    {
        if ($task['done']) {
            return 'Fait';
        }

        // Label libre saisi en back-office, sinon valeur par défaut du type.
        if (! empty($task['label'])) {
            return $task['label'];
        }

        return match ($task['tag_type']) {
            'mental' => 'Mental',
            'challenge' => 'Défi',
            default => 'À faire',
        };
    }
}
