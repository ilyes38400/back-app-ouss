<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProgramPurchase;
use App\Models\Workout;
use App\Models\MentalPreparation;
use App\Models\User;
use App\Models\PaymentGateway;
use App\Traits\ProgramAccessTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class ProgramPurchaseController extends Controller
{
    use ProgramAccessTrait;

    /**
     * Vérifier et créer un achat de programme
     */
    public function verifyAndCreatePurchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:ec_customers,id',
            'program_id' => 'required|integer',
            'program_type' => 'required|in:workout,mental',
            'platform' => 'required|in:apple,google,stripe',
            'platform_transaction_id' => 'required|string',
            'platform_product_id' => 'required|string',
            'receipt_data' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $request->all();
        $userId = $data['user_id'];
        $programId = $data['program_id'];
        $programType = $data['program_type'];

        try {
            // Vérifier si l'utilisateur existe
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Utilisateur introuvable'
                ], 404);
            }

            // Récupérer le programme
            $program = $this->getProgram($programId, $programType);
            if (!$program) {
                return response()->json([
                    'status' => false,
                    'message' => 'Programme introuvable'
                ], 404);
            }

            // Vérifier que le programme est bien payant
            if ($program->program_type !== 'paid') {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce programme n\'est pas disponible à l\'achat'
                ], 400);
            }

            // Vérifier si déjà acheté ET que le programme existe encore
            $existingPurchase = ProgramPurchase::active()
                ->forUser($userId)
                ->byProgram($programId, $programType)
                ->first();

            if ($existingPurchase) {
                // Vérifier que le programme acheté existe encore
                $programStillExists = false;
                if ($programType === 'workout') {
                    $programStillExists = \App\Models\Workout::where('id', $programId)->where('status', 'active')->exists();
                } elseif ($programType === 'mental') {
                    $programStillExists = \App\Models\MentalPreparation::where('id', $programId)->where('status', 'active')->exists();
                }

                if ($programStillExists) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Programme déjà acheté'
                    ], 400);
                } else {
                    // Le programme a été supprimé, marquer l'achat comme expiré
                    $existingPurchase->update(['status' => 'expired']);
                    Log::info('Achat marqué comme expiré car programme supprimé', [
                        'purchase_id' => $existingPurchase->id,
                        'program_id' => $programId,
                        'program_type' => $programType
                    ]);
                }
            }

            // Créer l'achat
            $purchaseData = [
                'user_id' => $userId,
                'program_id' => $programId,
                'program_type' => $programType,
                'program_title' => $program->title,
                'program_data' => $program->toArray(),
                'purchase_platform' => $data['platform'],
                'platform_transaction_id' => $data['platform_transaction_id'],
                'platform_product_id' => $data['platform_product_id'],
                'receipt_data' => $data['receipt_data'] ?? null,
                'purchase_date' => now(),
                'price' => $data['price'],
                'currency' => $data['currency'],
                'status' => 'active'
            ];

            $purchase = ProgramPurchase::create($purchaseData);

            Log::info('Programme acheté avec succès', [
                'user_id' => $userId,
                'program_id' => $programId,
                'program_type' => $programType,
                'purchase_id' => $purchase->id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Achat effectué avec succès',
                'data' => [
                    'purchase_id' => $purchase->id,
                    'program_title' => $program->title,
                    'access_granted' => true
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'achat du programme', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'program_id' => $programId,
                'program_type' => $programType
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de l\'achat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les programmes achetés par l'utilisateur
     */
    public function getUserPurchases(Request $request)
    {
        $userId = $request->input('user_id') ?? auth()->id();

        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $purchases = ProgramPurchase::active()
            ->forUser($userId)
            ->with(['workout', 'mentalPreparation'])
            ->orderBy('purchase_date', 'desc')
            ->get();

        $formattedPurchases = $purchases->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'program_id' => $purchase->program_id,
                'program_type' => $purchase->program_type,
                'program_title' => $purchase->program_title,
                'purchase_date' => $purchase->purchase_date->format('Y-m-d H:i:s'),
                'price' => $purchase->price,
                'currency' => $purchase->currency,
                'status' => $purchase->status
            ];
        });

        return json_custom_response([
            'data' => $formattedPurchases
        ]);
    }

    /**
     * Vérifier l'accès à un programme spécifique
     */
    public function checkProgramAccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:ec_customers,id',
            'program_id' => 'required|integer',
            'program_type' => 'required|in:workout,mental'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $userId = $request->input('user_id');
        $programId = $request->input('program_id');
        $programType = $request->input('program_type');

        $program = $this->getProgram($programId, $programType);
        if (!$program) {
            return response()->json([
                'status' => false,
                'message' => 'Programme introuvable'
            ], 404);
        }

        $hasAccess = $this->hasAccessToProgram($program, $userId);
        $accessReason = $this->getAccessReason($program, $userId);

        return response()->json([
            'status' => true,
            'data' => [
                'program_id' => $programId,
                'program_type' => $programType,
                'program_title' => $program->title,
                'program_access_type' => $program->program_type,
                'price' => $program->price,
                'has_access' => $hasAccess,
                'access_reason' => $accessReason,
                'requires_purchase' => $program->program_type === 'paid' && !$hasAccess,
                'requires_subscription' => $program->program_type === 'premium' && !$hasAccess
            ]
        ]);
    }

    /**
     * Récupérer un programme selon son type
     */
    private function getProgram($programId, $programType)
    {
        if ($programType === 'workout') {
            return Workout::find($programId);
        } elseif ($programType === 'mental') {
            return MentalPreparation::find($programId);
        }

        return null;
    }

    /**
     * Créer un PaymentIntent Stripe pour l'achat d'un programme
     */
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'program_id' => 'required|integer',
            'program_type' => 'required|in:workout,mental',
            'amount' => 'required|integer|min:1', // En centimes
            'currency' => 'required|string|max:3',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        try {
            // Récupérer les clés Stripe depuis les settings
            $stripeGateway = PaymentGateway::where('type', 'stripe')->where('status', 1)->first();
            if (!$stripeGateway) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stripe n\'est pas configuré ou activé'
                ], 400);
            }

            // Utiliser les clés test ou live selon la configuration
            $stripeKeys = $stripeGateway->is_test ? $stripeGateway->test_value : $stripeGateway->live_value;

            Log::info('Stripe Keys Debug', [
                'is_test' => $stripeGateway->is_test,
                'keys_exist' => !empty($stripeKeys),
                'keys_structure' => $stripeKeys ? array_keys($stripeKeys) : null
            ]);

            if (!$stripeKeys || !isset($stripeKeys['secret_key']) || empty($stripeKeys['secret_key'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Clé secrète Stripe non configurée',
                    'debug' => [
                        'is_test' => $stripeGateway->is_test,
                        'has_keys' => !empty($stripeKeys),
                        'available_keys' => $stripeKeys ? array_keys($stripeKeys) : null
                    ]
                ], 400);
            }

            // Configurer Stripe
            Stripe::setApiKey($stripeKeys['secret_key']);

            // Vérifier que le programme existe et est bien payant
            $program = $this->getProgram($request->program_id, $request->program_type);
            if (!$program) {
                return response()->json([
                    'status' => false,
                    'message' => 'Programme introuvable'
                ], 404);
            }

            if ($program->program_type !== 'paid') {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce programme n\'est pas disponible à l\'achat'
                ], 400);
            }

            // Vérifier si déjà acheté
            $existingPurchase = ProgramPurchase::active()
                ->forUser($userId)
                ->byProgram($request->program_id, $request->program_type)
                ->first();

            if ($existingPurchase) {
                return response()->json([
                    'status' => false,
                    'message' => 'Programme déjà acheté'
                ], 400);
            }

            // Créer le PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => $request->currency,
                'description' => $request->description ?? "Achat du programme {$program->title}",
                'metadata' => [
                    'user_id' => $userId,
                    'program_id' => $request->program_id,
                    'program_type' => $request->program_type,
                    'program_title' => $program->title
                ]
            ]);

            return response()->json([
                'status' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Erreur Stripe API lors de la création du PaymentIntent', [
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode(),
                'user_id' => $userId,
                'program_id' => $request->program_id,
                'program_type' => $request->program_type
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Erreur Stripe: ' . $e->getMessage(),
                'stripe_code' => $e->getStripeCode()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Erreur générale lors de la création du PaymentIntent', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'program_id' => $request->program_id,
                'program_type' => $request->program_type
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la création du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmer l'achat et accorder l'accès au programme
     */
    public function confirmPurchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'program_id' => 'required|integer',
            'program_type' => 'required|in:workout,mental',
            'payment_intent_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        try {
            // Récupérer les clés Stripe depuis les settings
            $stripeGateway = PaymentGateway::where('type', 'stripe')->where('status', 1)->first();
            if (!$stripeGateway) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stripe n\'est pas configuré ou activé'
                ], 400);
            }

            // Utiliser les clés test ou live selon la configuration
            $stripeKeys = $stripeGateway->is_test ? $stripeGateway->test_value : $stripeGateway->live_value;

            Log::info('Stripe Keys Debug', [
                'is_test' => $stripeGateway->is_test,
                'keys_exist' => !empty($stripeKeys),
                'keys_structure' => $stripeKeys ? array_keys($stripeKeys) : null
            ]);

            if (!$stripeKeys || !isset($stripeKeys['secret_key']) || empty($stripeKeys['secret_key'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Clé secrète Stripe non configurée',
                    'debug' => [
                        'is_test' => $stripeGateway->is_test,
                        'has_keys' => !empty($stripeKeys),
                        'available_keys' => $stripeKeys ? array_keys($stripeKeys) : null
                    ]
                ], 400);
            }

            // Configurer Stripe
            Stripe::setApiKey($stripeKeys['secret_key']);

            // Récupérer et vérifier le PaymentIntent
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'status' => false,
                    'message' => 'Le paiement n\'a pas été confirmé'
                ], 400);
            }

            // Vérifier que le PaymentIntent correspond aux données de la requête
            $metadata = $paymentIntent->metadata;
            if ($metadata['user_id'] != $userId ||
                $metadata['program_id'] != $request->program_id ||
                $metadata['program_type'] != $request->program_type) {
                return response()->json([
                    'status' => false,
                    'message' => 'Les données du paiement ne correspondent pas'
                ], 400);
            }

            // Vérifier que le programme existe
            $program = $this->getProgram($request->program_id, $request->program_type);
            if (!$program) {
                return response()->json([
                    'status' => false,
                    'message' => 'Programme introuvable'
                ], 404);
            }

            // Vérifier si déjà enregistré
            $existingPurchase = ProgramPurchase::where('platform_transaction_id', $request->payment_intent_id)->first();
            if ($existingPurchase) {
                return response()->json([
                    'status' => true,
                    'message' => 'Achat déjà confirmé'
                ]);
            }

            // Vérifier si l'utilisateur a déjà acheté ce programme ET qu'il existe encore
            $duplicatePurchase = ProgramPurchase::active()
                ->forUser($userId)
                ->byProgram($request->program_id, $request->program_type)
                ->first();

            if ($duplicatePurchase) {
                // Vérifier que le programme acheté existe encore
                $programStillExists = false;
                if ($request->program_type === 'workout') {
                    $programStillExists = \App\Models\Workout::where('id', $request->program_id)->where('status', 'active')->exists();
                } elseif ($request->program_type === 'mental') {
                    $programStillExists = \App\Models\MentalPreparation::where('id', $request->program_id)->where('status', 'active')->exists();
                }

                if ($programStillExists) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Programme déjà acheté'
                    ], 400);
                } else {
                    // Le programme a été supprimé, marquer l'achat comme expiré
                    $duplicatePurchase->update(['status' => 'expired']);
                    Log::info('Achat marqué comme expiré car programme supprimé', [
                        'purchase_id' => $duplicatePurchase->id,
                        'program_id' => $request->program_id,
                        'program_type' => $request->program_type
                    ]);
                }
            }

            // Enregistrer l'achat dans la base de données
            $purchaseData = [
                'user_id' => $userId,
                'program_id' => $request->program_id,
                'program_type' => $request->program_type,
                'program_title' => $program->title,
                'program_data' => $program->toArray(),
                'purchase_platform' => 'stripe',
                'platform_transaction_id' => $request->payment_intent_id,
                'platform_product_id' => "program_{$request->program_type}_{$request->program_id}",
                'receipt_data' => [
                    'payment_intent' => $paymentIntent->toArray()
                ],
                'purchase_date' => now(),
                'price' => $paymentIntent->amount / 100, // Convertir centimes en euros
                'currency' => strtoupper($paymentIntent->currency),
                'status' => 'active'
            ];

            $purchase = ProgramPurchase::create($purchaseData);

            Log::info('Achat de programme confirmé avec succès', [
                'user_id' => $userId,
                'program_id' => $request->program_id,
                'program_type' => $request->program_type,
                'purchase_id' => $purchase->id,
                'payment_intent_id' => $request->payment_intent_id
            ]);

            return response()->json([
                'status' => true,
                'success' => true,
                'message' => 'Achat confirmé avec succès',
                'purchase_id' => $purchase->id
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Erreur Stripe API lors de la confirmation de l\'achat', [
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode(),
                'user_id' => $userId,
                'program_id' => $request->program_id,
                'program_type' => $request->program_type,
                'payment_intent_id' => $request->payment_intent_id
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Erreur Stripe: ' . $e->getMessage(),
                'stripe_code' => $e->getStripeCode()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Erreur générale lors de la confirmation de l\'achat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'program_id' => $request->program_id,
                'program_type' => $request->program_type,
                'payment_intent_id' => $request->payment_intent_id
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la confirmation de l\'achat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déterminer la raison de l'accès (méthode privée du trait)
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