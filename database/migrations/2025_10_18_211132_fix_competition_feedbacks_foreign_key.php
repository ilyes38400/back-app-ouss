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
        Schema::table('competition_feedbacks', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte de clé étrangère
            $table->dropForeign(['user_id']);

            // Ajouter la nouvelle contrainte pointant vers ec_customers
            $table->foreign('user_id')->references('id')->on('ec_customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_feedbacks', function (Blueprint $table) {
            // Supprimer la contrainte corrigée
            $table->dropForeign(['user_id']);

            // Remettre l'ancienne contrainte vers users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
