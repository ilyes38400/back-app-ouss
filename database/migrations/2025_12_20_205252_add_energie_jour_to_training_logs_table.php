<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            // Ajouter le champ energie_jour (échelle 0-10)
            $table->decimal('energie_jour', 3, 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            // Supprimer le champ energie_jour
            $table->dropColumn('energie_jour');
        });
    }
};
