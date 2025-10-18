<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;

class AppleNotificationController extends Controller
{
    public function handleNotification(Request $request)
    {
        // Lecture du body brut
        $data = $request->getContent();
        $json = json_decode($data);
        $signedPayload = $json->signedPayload ?? null;
        
        if (!$signedPayload) {
            Log::error('signedPayload manquant dans la requête.');
            return response()->json(['error' => 'signedPayload manquant'], 400);
        }
        
        // Séparer le JWT en 3 parties
        $header_payload_secret = explode('.', $signedPayload);
        if (count($header_payload_secret) < 3) {
            Log::error('JWT invalide : 3 parties attendues.');
            return response()->json(['error' => 'JWT invalide'], 400);
        }

        //------------------------------------------
        // Extraction des certs + vérification
        //------------------------------------------
        $header = json_decode(base64_decode($header_payload_secret[0]));
        $algorithm = $header->alg ?? null;
        
        if (!isset($header->x5c) || !is_array($header->x5c) || count($header->x5c) < 3) {
            Log::error('Certificats manquants ou incomplets dans le header JWT.');
            return response()->json(['error' => 'Certificats manquants'], 400);
        }

        $x5c = $header->x5c;
        $certificate = "-----BEGIN CERTIFICATE-----\n" . $x5c[0] . "\n-----END CERTIFICATE-----";
        $intermediate_certificate = "-----BEGIN CERTIFICATE-----\n" . $x5c[1] . "\n-----END CERTIFICATE-----";
        $root_certificate = "-----BEGIN CERTIFICATE-----\n" . $x5c[2] . "\n-----END CERTIFICATE-----";

        // Cert racine Apple (déposé localement)
        $pem = file_get_contents(storage_path('app/certs/apple_root.pem'));

        // Vérif chainage
        if (openssl_x509_verify($intermediate_certificate, $root_certificate) != 1) {
            Log::error('Les certificats intermédiaire et racine ne correspondent pas.');
            return response()->json(['error' => 'Certificats incompatibles'], 500);
        }
        
        if (openssl_x509_verify($root_certificate, $pem) !== 1) {
            Log::error('Certificat racine Apple invalide ou non reconnu.');
            return response()->json(['error' => 'Header non valide'], 400);
        }

        // Ok, on peut extraire la clé publique
        $cert_object = openssl_x509_read($certificate);
        $pkey_object = openssl_pkey_get_public($cert_object);
        $pkey_array = openssl_pkey_get_details($pkey_object);
        $publicKey = $pkey_array['key'] ?? null;

        if (!$publicKey) {
            Log::error('Impossible de récupérer la clé publique Apple.');
            return response()->json(['error' => 'Clé publique absente'], 400);
        }
        Log::info('Clé publique extraite avec succès.');

        //------------------------------------------
        // Traitement du payload (deuxième partie du JWT)
        //------------------------------------------
        $payload = json_decode(base64_decode($header_payload_secret[1]));
        if (!$payload) {
            Log::error('Impossible de décoder le payload.');
            return response()->json(['error' => 'Payload invalide'], 400);
        }

        Log::info('Payload décodé (avant traitement):', (array)$payload);

        $notificationType = $payload->notificationType ?? null;
        Log::info('Type de notification:', ['notificationType' => $notificationType]);
        
        // Vérif si on a signedTransactionInfo
        if (!isset($payload->data->signedTransactionInfo)) {
            Log::warning('Aucune signedTransactionInfo trouvée dans le payload.');
            return response()->json(['status' => 'success', 'data' => $payload], 200);
        }
        
        $transactionInfo = $payload->data->signedTransactionInfo;
        $signedRenewalInfo = $payload->data->signedRenewalInfo ?? null;
        
        // Décodage du signedTransactionInfo
        $originalTransactionId = null;
        $expiresDate = null; // en ms ou en s, Apple doc v2 => ms en général

        try {
            $transactionDecodedData = JWT::decode($transactionInfo, new Key($publicKey, $algorithm));
            $transactionDecodedData = (array)$transactionDecodedData; // cast en array pour simplifier
            Log::info('Transaction info décodée:', $transactionDecodedData);

            $originalTransactionId = $transactionDecodedData['originalTransactionId'] ?? null;
            // Expires date (Apple en ms)
            if (isset($transactionDecodedData['expiresDate'])) {
                $expiresDate = $transactionDecodedData['expiresDate'];
            }

            if ($originalTransactionId) {
                Log::info('Original Transaction ID:', ['originalTransactionId' => $originalTransactionId]);
            } else {
                Log::warning('originalTransactionId non trouvé dans signedTransactionInfo.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du décodage de signedTransactionInfo : ' . $e->getMessage());
        }

        // Décodage du signedRenewalInfo si besoin
        if ($signedRenewalInfo) {
            try {
                $signedRenewalDecodedData = JWT::decode($signedRenewalInfo, new Key($publicKey, $algorithm));
                $signedRenewalDecodedData = (array)$signedRenewalDecodedData;
                Log::info('Renewal info décodée:', $signedRenewalDecodedData);
            } catch (\Exception $e) {
                Log::error('Erreur lors du décodage de signedRenewalInfo : ' . $e->getMessage());
            }
        }

        //------------------------------------------
        // Mise à jour de la souscription en BDD
        //------------------------------------------
        if ($originalTransactionId) {
            // Par exemple, si ta table s'appelle app_subscriptions
            // et que tu stockes l'originalTransactionId dans uniqueIdentifier
            $subscription = Subscription::where('unique_identifier', $originalTransactionId)->first();
            
            if (!$subscription) {
                Log::warning("Subscription introuvable pour originalTransactionId: {$originalTransactionId}");
                // Option : si c'est SUBSCRIBED (achat initial), tu peux créer un enregistrement
                // ...
            } else {
                // On récupère l'utilisateur
                $user = $subscription->user;

                // Calculer la date d'expiration en Carbon si on a $expiresDate (ms)
                if ($expiresDate) {
                    // Apple doc v2 => expiration est en millisecondes
                    $expiresUnix = floor($expiresDate / 1000);
                    $expirationDateTime = Carbon::createFromTimestamp($expiresUnix);
                } else {
                    // s'il n'y a pas de expiresDate => on laisse tel quel ou on met null
                    $expirationDateTime = $subscription->subscription_end_date;
                }

                // Switch selon le type de notification
                switch ($notificationType) {
                    case 'SUBSCRIBED':
                    case 'DID_RENEW':
                        $subscription->status = 'active';
                        if ($expirationDateTime) {
                            $subscription->subscription_end_date = $expirationDateTime;
                        }
                        $subscription->save();
                
                        if ($user) {
                            $user->is_subscribe = $subscription->subscription_end_date && $subscription->subscription_end_date->gt(now()) ? 1 : 0;
                            $user->save();
                        }
                        break;
                
                    case 'REFUND':
                    case 'REVOKE':
                        $subscription->status = 'canceled';
                        $subscription->subscription_end_date = now();
                        $subscription->save();
                
                        if ($user) {
                            $user->is_subscribe = 0;
                            $user->save();
                        }
                        break;
                
                    case 'DID_CHANGE_RENEWAL_STATUS':
                        // auto_renew_status (optionnel)
                        $subscription->status = 'active'; // still valid
                        if ($expirationDateTime) {
                            $subscription->subscription_end_date = $expirationDateTime;
                        }
                        $subscription->save();
                
                        if ($user) {
                            $user->is_subscribe = $subscription->subscription_end_date && $subscription->subscription_end_date->gt(now()) ? 1 : 0;
                            $user->save();
                        }
                        break;
                
                    case 'DID_FAIL_TO_RENEW':
                        // Peut entrer en grace period ou retry, donc toujours actif
                        $subscription->status = 'active';
                        if ($expirationDateTime) {
                            $subscription->subscription_end_date = $expirationDateTime;
                        }
                        $subscription->save();
                
                        if ($user) {
                            $user->is_subscribe = $subscription->subscription_end_date && $subscription->subscription_end_date->gt(now()) ? 1 : 0;
                            $user->save();
                        }
                        break;
                
                    // Facultatif : gérer l’expiration réelle
                    case 'EXPIRED':
                        $subscription->status = 'canceled';
                        $subscription->subscription_end_date = now();
                        $subscription->save();
                
                        if ($user) {
                            $user->is_subscribe = 0;
                            $user->save();
                        }
                        break;
                
                    default:
                        Log::info("🔔 Notification Apple non gérée : {$notificationType}");
                        break;
                }
                

                // Optionnel : garder une trace du dernier payload
                // ex:
                // $subscription->transaction_detail = ['lastPayload' => $transactionDecodedData];
                // $subscription->save();
            }
        }

        // Fin
        return response()->json(['status' => 'success', 'data' => $payload], 200);
    }
}
