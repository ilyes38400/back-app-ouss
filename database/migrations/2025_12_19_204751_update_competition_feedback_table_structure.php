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
            // Supprimer les anciennes colonnes
            $table->dropColumn([
                'motivation',
                'focus',
                'mental_presence',
                'clear_objective',
                'emotional_stability',
                'decision_making',
                'competition_entry',
                'maximum_effort',
                'automaticity',
                'ideal_self_rating'
            ]);

            // Ajouter les nouvelles colonnes DECIMAL(3,1)
            // Attention
            $table->decimal('full_mindfulness', 3, 1)->nullable();
            $table->decimal('objective_clarity', 3, 1)->nullable();
            $table->decimal('letting_go', 3, 1)->nullable();
            $table->decimal('decision_relevance', 3, 1)->nullable();

            // Engagement
            $table->decimal('activation', 3, 1)->nullable();
            $table->decimal('engagement', 3, 1)->nullable();
            $table->decimal('initiative', 3, 1)->nullable();

            // Ressentis (garder physical_sensations et stress_tension existantes)
            $table->decimal('flow_confidence', 3, 1)->nullable();
            $table->decimal('emotional_management', 3, 1)->nullable();

            // Performance
            $table->decimal('performance_satisfaction', 3, 1)->nullable();
            $table->decimal('max_level_rating', 3, 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_feedbacks', function (Blueprint $table) {
            // Supprimer les nouvelles colonnes
            $table->dropColumn([
                'full_mindfulness',
                'objective_clarity',
                'letting_go',
                'decision_relevance',
                'activation',
                'engagement',
                'initiative',
                'flow_confidence',
                'emotional_management',
                'performance_satisfaction',
                'max_level_rating'
            ]);

            // Rétablir les anciennes colonnes
            $table->decimal('motivation', 3, 1)->nullable();
            $table->decimal('focus', 3, 1)->nullable();
            $table->decimal('mental_presence', 3, 1)->nullable();
            $table->string('clear_objective')->nullable();
            $table->decimal('emotional_stability', 3, 1)->nullable();
            $table->decimal('decision_making', 3, 1)->nullable();
            $table->decimal('competition_entry', 3, 1)->nullable();
            $table->decimal('maximum_effort', 3, 1)->nullable();
            $table->decimal('automaticity', 3, 1)->nullable();
            $table->decimal('ideal_self_rating', 3, 1)->nullable();
        });
    }
};
