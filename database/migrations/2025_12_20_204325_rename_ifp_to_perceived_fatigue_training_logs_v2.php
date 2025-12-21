<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            // Ajouter la nouvelle colonne perceived_fatigue
            $table->decimal('perceived_fatigue', 3, 1)->nullable();
        });

        // Copier les données de ifp vers perceived_fatigue
        DB::statement('UPDATE training_logs SET perceived_fatigue = ifp');

        Schema::table('training_logs', function (Blueprint $table) {
            // Supprimer l'ancienne colonne ifp
            $table->dropColumn('ifp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            // Ajouter l'ancienne colonne ifp
            $table->decimal('ifp', 3, 1)->nullable();
        });

        // Copier les données de perceived_fatigue vers ifp
        DB::statement('UPDATE training_logs SET ifp = perceived_fatigue');

        Schema::table('training_logs', function (Blueprint $table) {
            // Supprimer la nouvelle colonne perceived_fatigue
            $table->dropColumn('perceived_fatigue');
        });
    }
};
