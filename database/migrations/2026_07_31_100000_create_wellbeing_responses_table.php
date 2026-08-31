<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Miroir local des réponses du questionnaire bien-être hebdomadaire.
     * La source de vérité reste selfperform.fr, mais on garde une copie ici
     * pour pouvoir calculer le score "Prêt à performer" sans dépendance réseau.
     */
    public function up(): void
    {
        Schema::create('wellbeing_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('submitted_at');

            // Récupération
            $table->float('sleep_quality')->nullable();
            $table->float('energy_wakeup')->nullable();
            $table->float('physical_fatigue')->nullable();   // inversé au calcul
            $table->float('body_pain')->nullable();          // inversé au calcul

            // Mental
            $table->float('global_stress')->nullable();      // inversé au calcul
            $table->float('control_feeling')->nullable();
            $table->float('mental_fatigue')->nullable();     // inversé au calcul
            $table->float('happiness')->nullable();

            // Hygiène
            $table->float('food_quality')->nullable();
            $table->float('natural_light')->nullable();      // pas encore de question côté selfperform
            $table->float('active_recovery')->nullable();

            // Toutes les réponses brutes {question_id: score} pour retraitement futur
            $table->json('raw_answers')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wellbeing_responses');
    }
};
