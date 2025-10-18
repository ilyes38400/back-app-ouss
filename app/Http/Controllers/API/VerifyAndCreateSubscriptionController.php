<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Google_Client;

class VerifyAndCreateSubscriptionController extends Controller
{
    /**
     * Endpoint unique pour vérifier le reçu et créer la souscription.
     */
    public function verifyAndCreateSubscription(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        // 🔥 Récupérer les données envoyées
        $receiptData = $request->input('receipt_data');
        $platform = $request->input('platform', 'ios');
        $productId = $request->input('product_id');
        $originalTransactionId = $request->input('original_transaction_id');

        if (empty($receiptData)) {
            Log::error('❌ Receipt data is empty');
            return response()->json([
                'status' => false,
                'message' => 'Purchase token (receipt_data) is missing'
            ], 400);
        }

        $subscriptionEndDate = null; // 🔥 Variable pour stocker la date d'expiration

        // 🔥 Vérification du reçu selon la plateforme
        if ($platform === 'ios') {
            try {
                // Vérification du reçu iOS via la librairie Readdle
                $appleIncRootCertificate = \Readdle\AppStoreReceiptVerification\Utils::DER2PEM(
                    file_get_contents('https://www.apple.com/appleca/AppleIncRootCertificate.cer')
                );
        
                $serializedReceipt = \Readdle\AppStoreReceiptVerification\AppStoreReceiptVerification::verifyReceipt(
                    $receiptData,
                    $appleIncRootCertificate
                );
        
                // 🔍 Décoder les données JSON du reçu
                $decoded = json_decode((string) $serializedReceipt, true);
        
                if (isset($decoded['latest_receipt_info']) && is_array($decoded['latest_receipt_info'])) {
                    $latest = end($decoded['latest_receipt_info']);
        
                    // 🔥 Logging clair
                    Log::info('✅ iOS Receipt verified', [
                        'transaction_id' => $latest['transaction_id'] ?? null,
                        'product_id' => $latest['product_id'] ?? null,
                        'purchase_date' => $latest['purchase_date'] ?? null,
                        'expires_date' => $latest['expires_date'] ?? null,
                        'user_id' => $user->id,
                    ]);
        
                    if (isset($latest['expires_date_ms'])) {
                        $subscriptionEndDate = Carbon::createFromTimestampMs($latest['expires_date_ms']);
                    }
                } else {
                    Log::warning('ℹ️ iOS Receipt: No latest_receipt_info found', ['user_id' => $user->id]);
                }
        
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Receipt invalid: ' . $e->getMessage()
                ], 400);
            }
        
            $uniqueIdentifier = $originalTransactionId;
        }
        elseif ($platform === 'android') {
            if (!$productId) {
                return response()->json([
                    'status' => false,
                    'message' => 'product_id is required for Android'
                ], 400);
            }

            try {
                $purchaseToken = $receiptData;
                $packageName = 'com.mfa.myfitnessapp';
                $client = new Client();
                $accessToken = $this->getGoogleAccessToken();
                $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptions/{$productId}/tokens/{$purchaseToken}";

                $googleResponse = $client->request('GET', $url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ]
                ]);

                $body = json_decode($googleResponse->getBody()->getContents(), true);

                // Vérifier si l'achat est valide
                if (isset($body['paymentState']) && $body['paymentState'] == 1) {
                    Log::info('✅ Google Play purchase verified successfully', ['response' => $body]);

                    // 🔥 Récupération de `expiryTimeMillis` pour `subscription_end_date`
                    $expiryTimeMillis = $body['expiryTimeMillis'] ?? null;
                    if ($expiryTimeMillis) {
                        $subscriptionEndDate = Carbon::createFromTimestampMs($expiryTimeMillis);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Android purchase is not valid'
                    ], 400);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error verifying Android receipt: ' . $e->getMessage()
                ], 400);
            }

            $uniqueIdentifier = $receiptData;
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unsupported platform'
            ], 400);
        }

        // 🔥 Création ou mise à jour de la souscription
        try {
            $subscription = Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'unique_identifier' => $uniqueIdentifier,
                    'platform' => $platform,
                    'product_id' => $productId,
                    'status' => 'active',
                    'subscription_end_date' => $subscriptionEndDate, // 🔥 Ajout de la date d'expiration
                ]
            );

            // 🔥 Mise à jour de `is_subscribe`
            $user->is_subscribe = ($subscription->subscription_end_date && $subscription->subscription_end_date->gt(now())) ? 1 : 0;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Subscription verified & created'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create/update subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 Récupère un access token pour l'API Google Play Android Publisher via le compte de service.
     */
    protected function getGoogleAccessToken()
    {
        $jsonKeyPath = storage_path('app/credentials/google_service_account.json');
        $client = new Google_Client();
        $client->setAuthConfig($jsonKeyPath);
        $client->addScope("https://www.googleapis.com/auth/androidpublisher");

        // Récupérer l'access token via assertion JWT
        $accessToken = $client->fetchAccessTokenWithAssertion();
        if (isset($accessToken['access_token'])) {
            return $accessToken['access_token'];
        } else {
            throw new \Exception("❌ Impossible d'obtenir l'Access Token Google: " . json_encode($accessToken));
        }
    }
}
