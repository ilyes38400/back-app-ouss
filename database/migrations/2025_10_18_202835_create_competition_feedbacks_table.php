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
        Schema::create('competition_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('competition_name');
            $table->date('competition_date');

            // Questions binaires (1 ou 2)
            $table->tinyInteger('situation_response'); // 1=Défi, 2=Menace
            $table->tinyInteger('victory_response');   // 1=Recherche victoire, 2=Évitement défaite

            // Notes sur 10
            $table->decimal('difficulty_level', 3, 1);
            $table->decimal('motivation', 3, 1);
            $table->decimal('focus', 3, 1);
            $table->decimal('negative_focus', 3, 1);
            $table->decimal('mental_presence', 3, 1);
            $table->decimal('physical_sensations', 3, 1);
            $table->decimal('emotional_stability', 3, 1);
            $table->decimal('stress_tension', 3, 1);
            $table->decimal('decision_making', 3, 1);
            $table->decimal('competition_entry', 3, 1);
            $table->decimal('maximum_effort', 3, 1);
            $table->decimal('automaticity', 3, 1);
            $table->decimal('ideal_self_rating', 3, 1);

            // Champs texte
            $table->text('clear_objective');
            $table->text('performance_comment')->nullable();

            $table->timestamps();

            // Index
            $table->index(['user_id', 'competition_date']);
            $table->index('competition_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_feedbacks');
    }
};
