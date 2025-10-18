<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use SubscriptionWebhooks\Laravel\SubscriptionWebhookReceived;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(SubscriptionWebhookReceived::class, function ($event) {
            $platform = $event->platform;
            $data = $event->webhookData;
    
            Log::info("Webhook reçu de $platform", $data);
    
            if ($platform === 'apple') {
                $notificationType = $data['notificationType'];
                $subtype = $data['subtype'] ?? null;
                self::handleAppleSubscription($data);
            } elseif ($platform === 'google') {
                $notificationType = $data['notificationType'];
                self::handleGoogleSubscription($data);
            }
        });
    
        }

        private static function handleAppleSubscription($data)
{
    $subscriptionId = $data['data']['subscriptionId'] ?? null;
    $uniqueIdentifier = $data['data']['transactionId'] ?? null;
    $expiryTimeMillis = $data['data']['expiresDate'] ?? null;
    $startTimeMillis = $data['data']['purchaseDate'] ?? null;
    $paymentStatus = $data['data']['status'] ?? 'pending';

    if (!$subscriptionId || !$uniqueIdentifier) {
        Log::error("Subscription ID ou Unique Identifier manquant dans le webhook Apple");
        return;
    }

    $subscription = Subscription::where('uniqueIdentifier', $uniqueIdentifier)->first();

    if (!$subscription) {
        Log::info("Création d’un nouvel abonnement Apple : $uniqueIdentifier");
        $subscription = new Subscription([
            'user_id' => null, // Associer avec l'utilisateur si possible
            'package_id' => null,
            'uniqueIdentifier' => $uniqueIdentifier,
            'payment_status' => $paymentStatus,
            'subscription_start_date' => Carbon::createFromTimestampMs($startTimeMillis),
            'subscription_end_date' => Carbon::createFromTimestampMs($expiryTimeMillis),
            'status' => 'active',
            'platform' => 'apple',
            'txn_id' => $subscriptionId,
            'transaction_detail' => json_encode($data),
        ]);
    } else {
        $subscription->update([
            'payment_status' => $paymentStatus,
            'subscription_end_date' => Carbon::createFromTimestampMs($expiryTimeMillis),
            'status' => 'active',
            'transaction_detail' => json_encode($data),
        ]);
    }

    $subscription->save();
    Log::info("Mise à jour de l'abonnement Apple : $uniqueIdentifier - Statut : $subscription->status");
}

private static function handleGoogleSubscription($data)
{
    $subscriptionId = $data['subscriptionId'] ?? null;
    $uniqueIdentifier = $data['purchaseToken'] ?? null;
    $expiryTimeMillis = $data['expiryTimeMillis'] ?? null;
    $startTimeMillis = $data['startTimeMillis'] ?? null;
    $paymentStatus = $data['paymentState'] ?? 'pending';

    if (!$subscriptionId || !$uniqueIdentifier) {
        Log::error("Subscription ID ou Unique Identifier manquant dans le webhook Google");
        return;
    }

    $subscription = Subscription::where('uniqueIdentifier', $uniqueIdentifier)->first();

    if (!$subscription) {
        Log::info("Création d’un nouvel abonnement Google : $uniqueIdentifier");
        $subscription = new Subscription([
            'user_id' => null,
            'package_id' => null,
            'uniqueIdentifier' => $uniqueIdentifier,
            'payment_status' => $paymentStatus,
            'subscription_start_date' => Carbon::createFromTimestampMs($startTimeMillis),
            'subscription_end_date' => Carbon::createFromTimestampMs($expiryTimeMillis),
            'status' => 'active',
            'platform' => 'google',
            'txn_id' => $subscriptionId,
            'transaction_detail' => json_encode($data),
        ]);
    } else {
        $subscription->update([
            'payment_status' => $paymentStatus,
            'subscription_end_date' => Carbon::createFromTimestampMs($expiryTimeMillis),
            'status' => 'active',
            'transaction_detail' => json_encode($data),
        ]);
    }

    $subscription->save();
    Log::info("Mise à jour de l'abonnement Google : $uniqueIdentifier - Statut : $subscription->status");
}

}
