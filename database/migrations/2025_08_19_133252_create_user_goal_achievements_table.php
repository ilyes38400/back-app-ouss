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
        Schema::create('user_goal_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('goal_challenge_id');
            $table->enum('goal_type', ['physique', 'alimentaire', 'mental']);
            $table->date('achieved_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('ec_customers')->onDelete('cascade');
            $table->foreign('goal_challenge_id')->references('id')->on('goal_challenges')->onDelete('cascade');
            
            $table->index(['user_id', 'goal_type']);
            $table->index(['user_id', 'achieved_at']);
            
            // Empêcher qu'un utilisateur puisse marquer le même objectif comme réussi plusieurs fois
            $table->unique(['user_id', 'goal_challenge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_goal_achievements');
    }
};
