<?php

namespace App\Traits;

use App\Models\ProgramPurchase;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

trait ProgramAccessTrait
{
    /**
     * Vérifie si l'utilisateur a accès à un programme
     */
    public function hasAccessToProgram($program, $userId)
    {
        // Programme gratuit : accès libre
        if ($program->program_type === 'free') {
            return true;
        }

        // Programme premium : vérifier abonnement
        if ($program->program_type === 'premium') {
            return $this->hasActiveSubscription($userId);
        }

        // Programme payant : vérifier achat individuel
        if ($program->program_type === 'paid') {
            return $this->hasPurchasedProgram($userId, $program);
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur a un abonnement actif
     */
    private function hasActiveSubscription($userId)
    {
        $validStatuses = ['active', 'canceled', 'on_hold', 'grace_period', 'paused', 'deferred'];

        return Subscription::where('user_id', $userId)
            ->whereIn('status', $validStatuses)
            ->where('subscription_end_date', '>=', now())
            ->exists();
    }

    /**
     * Vérifie si l'utilisateur a acheté le programme
     */
    private function hasPurchasedProgram($userId, $program)
    {
        try {
            $programType = $this->getProgramType($program);

            // MÊME LOGIQUE que ProgramPurchaseController
            $purchase = ProgramPurchase::active()
                ->forUser($userId)
                ->byProgram($program->id, $programType)
                ->first();

            if (!$purchase) {
                return false;
            }

            // Si achat trouvé, vérifier que le programme existe encore
            $programStillExists = false;
            if ($programType === 'workout') {
                $programStillExists = \App\Models\Workout::where('id', $program->id)->where('status', 'active')->exists();
            } elseif ($programType === 'mental') {
                $programStillExists = \App\Models\MentalPreparation::where('id', $program->id)->where('status', 'active')->exists();
            }

            if ($programStillExists) {
                return true;
            } else {
                // Programme supprimé, marquer l'achat comme expiré
                $purchase->update(['status' => 'expired']);
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('Erreur dans hasPurchasedProgram: ' . $e->getMessage(), [
                'program_id' => $program->id ?? 'unknown',
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * Détermine le type de programme (workout ou mental)
     */
    private function getProgramType($program)
    {
        if (get_class($program) === 'App\Models\Workout') {
            return 'workout';
        } elseif (get_class($program) === 'App\Models\MentalPreparation') {
            return 'mental';
        }

        return 'unknown';
    }

    /**
     * Filtre les programmes selon l'accès utilisateur
     */
    public function filterProgramsByAccess($programs, $userId)
    {
        return $programs->filter(function ($program) use ($userId) {
            return $this->hasAccessToProgram($program, $userId);
        });
    }

    /**
     * Ajoute les informations d'accès à un programme
     */
    public function addAccessInfo($program, $userId)
    {
        try {
            $hasAccess = $this->hasAccessToProgram($program, $userId);
            $accessReason = $this->getAccessReason($program, $userId);

            // Modifier directement l'objet
            $program->user_has_access = $hasAccess;
            $program->access_reason = $accessReason;
            $program->requires_purchase = $program->program_type === 'paid' && !$hasAccess;
            $program->requires_subscription = $program->program_type === 'premium' && !$hasAccess;

            return $program;
        } catch (\Exception $e) {
            // En cas d'erreur, retourner des valeurs par défaut
            \Log::error('Erreur dans addAccessInfo: ' . $e->getMessage(), [
                'program_id' => $program->id ?? 'unknown',
                'program_type' => $program->program_type ?? 'unknown',
                'user_id' => $userId
            ]);

            $program->user_has_access = $program->program_type === 'free';
            $program->access_reason = 'error';
            $program->requires_purchase = $program->program_type === 'paid';
            $program->requires_subscription = $program->program_type === 'premium';

            return $program;
        }
    }

    /**
     * Détermine la raison de l'accès
     */
    private function getAccessReason($program, $userId)
    {
        if ($program->program_type === 'free') {
            return 'free_program';
        }

        if ($program->program_type === 'premium' && $this->hasActiveSubscription($userId)) {
            return 'subscription';
        }

        if ($program->program_type === 'paid' && $this->hasPurchasedProgram($userId, $program)) {
            return 'purchased';
        }

        return 'no_access';
    }
}