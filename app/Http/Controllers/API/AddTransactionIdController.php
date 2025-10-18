<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use phpseclib3\File\ASN1;

class AddTransactionIdController extends Controller
{
    public function addTransactionId(Request $request)
    {
        $data = $request->json()->all();
    
        if (!isset($data['user_id']) || !isset($data['original_transaction_id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paramètres manquants'
            ], 400);
        }
    
        $userId = $data['user_id'];
        $transactionId = $data['original_transaction_id'];
    
        // Rechercher l'abonnement de l'utilisateur
        $subscription = Subscription::where('user_id', $userId)->first();
    
        if (!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Abonnement non trouvé'
            ], 404);
        }
    
        // Mettre à jour la colonne original_transaction_id avec le transactionId
        $subscription->original_transaction_id = $transactionId;
        $subscription->save();
    
        return response()->json([
            'status' => 'success',
            'data'   => [
                'original_transaction_id' => $transactionId,
                'attributes' => [
                    'user_id' => $userId,
                    'subscription_id' => $subscription->id,
                    'original_transaction_id' => $subscription->original_transaction_id,
                ],
            ]
        ], 200);
    }
    
}
