<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAppSubscriptionsTable extends Migration
{
    /**
     * Exécute les migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_subscriptions', function (Blueprint $table) {
            $table->string('unique_identifier')->unique()->after('id'); // Clé unique pour iOS et Android
        });
    }

    /**
     * Annule les migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'unique_identifier',
            ]);
        });
    }
}
