<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ce fichier avait été renommé après avoir été joué, ce qui cassait
 * `php artisan migrate` : Laravel le voyait comme une nouvelle migration et
 * dérivait de son nom une classe (UpdateAppSubscriptionsTables) différente de
 * celle déclarée (UpdateAppSubscriptionsTable).
 *
 * Le nom de fichier a été remis en accord avec la table `migrations`, et la
 * classe nommée est remplacée par une classe anonyme pour que le problème ne
 * puisse plus se reproduire. Les opérations sont idempotentes : la migration
 * passe qu'elle ait déjà été appliquée ou non.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('app_subscriptions', 'unique_identifier')) {
            return;
        }

        Schema::table('app_subscriptions', function (Blueprint $table) {
            // Clé unique de la transaction côté iOS et Android.
            $table->string('unique_identifier')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('app_subscriptions', 'unique_identifier')) {
            return;
        }

        Schema::table('app_subscriptions', function (Blueprint $table) {
            $table->dropColumn('unique_identifier');
        });
    }
};
