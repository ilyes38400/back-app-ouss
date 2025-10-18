<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Google_Client;

class GoogleNotificationController extends Controller
{
    public function handleNotification(Request $request)
    {
        // Lecture du body brut
        $data = file_get_contents('php://input');
        $json = json_decode($data, true);

        if (!isset($json['message']['data'])) {
            Log::error('❌ Google Play Webhook : data manquant');
            return response()->json(['error' => 'Data manquant'], 400);
        }

        // 🔥 Décodage Base64 du message
        $decodedPayload = json_decode(base64_decode($json['message']['data']), true);
        if (isset($decodedPayload['testNotification'])) {
            Log::info('🛑 Notification de test Google Play reçue. Ignorée.');
            return response()->json(['message' => 'Test notification ignored'], 200);
        }

        if (!$decodedPayload) {
            Log::error('❌ Google Play Webhook : Échec du décodage Base64');
            return response()->json(['error' => 'Décodage échoué'], 400);
        }

        Log::info('📩 Payload Google Play décodé:', $decodedPayload);

        //------------------------------------------
        // 🔥 Extraction des informations importantes
        //------------------------------------------
        $notificationType = $decodedPayload['subscriptionNotification']['notificationType'] ?? null;
        $purchaseToken = $decodedPayload['subscriptionNotification']['purchaseToken'] ?? null;
        $subscriptionId = $decodedPayload['subscriptionNotification']['subscriptionId'] ?? null;
        $expiryTimeMillis = $decodedPayload['subscriptionNotification']['expiryTimeMillis'] ?? null;

        if (!$notificationType || !$purchaseToken) {
            Log::error('❌ Google Play Webhook : Données essentielles manquantes', $decodedPayload);
            return response()->json(['error' => 'Données incomplètes'], 400);
        }

        // 🔥 Récupération de l'abonnement existant
        $subscription = Subscription::where('unique_identifier', $purchaseToken)->first();

        if (!$subscription) {
            Log::warning("🛑 Google Play Webhook : Abonnement introuvable pour purchaseToken: {$purchaseToken}");
            return response()->json(['status' => 'ignored', 'reason' => 'Subscription not found'], 200);
        }

        // 🔥 Récupérer expiryTimeMillis via API Google Play si absent
        if (!$expiryTimeMillis) {
            Log::warning("⚠️ expiryTimeMillis absent, récupération depuis API Google Play...");

            $subscriptionDetails = $this->getSubscriptionDetailsFromGoogle($purchaseToken, $subscriptionId);
            if ($subscriptionDetails && isset($subscriptionDetails['expiryTimeMillis'])) {
                $expiryTimeMillis = $subscriptionDetails['expiryTimeMillis'];
                Log::info("✅ Date d'expiration récupérée via API Google : " . Carbon::createFromTimestampMs($expiryTimeMillis));
            } else {
                Log::error("❌ Impossible de récupérer expiryTimeMillis via API Google Play.");
            }
        }

        //------------------------------------------
        // 🔥 Mise à jour de l'abonnement
        //------------------------------------------
        switch ($notificationType) {
            case 1: // SUBSCRIPTION_RECOVERED
            case 2: // SUBSCRIPTION_RENEWED
            case 4: // SUBSCRIPTION_PURCHASED
            case 7: // SUBSCRIPTION_RESTARTED
                $subscription->status = 'active';
                break;
        
            case 3: // SUBSCRIPTION_CANCELED
                $subscription->status = 'canceled'; // ⚠️ L'utilisateur peut encore avoir accès jusqu'à `subscription_end_date`
                break;
        
            case 12: // SUBSCRIPTION_REVOKED
            case 13: // SUBSCRIPTION_EXPIRED
                $subscription->status = 'expired'; // ⚠️ Plus d'accès après `subscription_end_date`
                break;
        
            case 5: // SUBSCRIPTION_ON_HOLD
                $subscription->status = 'on_hold';
                break;
        
            case 6: // SUBSCRIPTION_IN_GRACE_PERIOD
                $subscription->status = 'grace_period';
                break;
        
            case 9: // SUBSCRIPTION_DEFERRED
                $subscription->status = 'deferred';
                break;
        
            case 10: // SUBSCRIPTION_PAUSED
                $subscription->status = 'paused';
                break;
        
            default:
                Log::warning("⚠️ Type de notification inconnu : {$notificationType}");
                break;
        }
        
        // 🔥 Toujours mettre à jour la date d’expiration si `expiryTimeMillis` est disponible
        if ($expiryTimeMillis) {
            $subscription->subscription_end_date = Carbon::createFromTimestampMs($expiryTimeMillis);
        }

        // 🔥 Sauvegarde de la mise à jour
        $subscription->transaction_detail = json_encode($decodedPayload);
        $subscription->save();

        Log::info("✅ Google Play : Abonnement mis à jour - PurchaseToken: {$purchaseToken} - Expiration: {$subscription->subscription_end_date}");

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * 🔥 Fonction pour récupérer expiryTimeMillis via Google Play API
     */
    private function getSubscriptionDetailsFromGoogle($purchaseToken, $subscriptionId)
    {
        $packageName = 'com.mfa.myfitnessapp'; // Remplace avec ton package

        try {
            // 🔥 Récupérer l'access token
            $accessToken = $this->getGoogleAccessToken();

            // 🔥 Appeler l'API Google Play avec l'access token
            $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptions/{$subscriptionId}/tokens/{$purchaseToken}";

            $response = Http::withToken($accessToken)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("❌ Google Play API : Échec récupération abonnement", [
                'purchaseToken' => $purchaseToken,
                'subscriptionId' => $subscriptionId,
                'error' => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Erreur lors de la récupération de l'abonnement Google Play", [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * 🔥 Fonction pour récupérer un Access Token Google Play
     */
    private function getGoogleAccessToken()
    {
        $jsonKeyPath = storage_path('app/credentials/google_service_account.json');

        $client = new Google_Client();
        $client->setAuthConfig($jsonKeyPath);
        $client->addScope("https://www.googleapis.com/auth/androidpublisher");

        // 🔥 Récupérer l'access token via JWT assertion
        $accessToken = $client->fetchAccessTokenWithAssertion();
        if (isset($accessToken['access_token'])) {
            return $accessToken['access_token'];
        } else {
            throw new \Exception("Impossible d'obtenir l'Access Token Google: " . json_encode($accessToken));
        }
    }
}
