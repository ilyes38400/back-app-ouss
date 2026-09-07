<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Trace des soumissions du questionnaire annuel.
     *
     * Les réponses elles-mêmes restent sur selfperform.fr, mais celui-ci
     * n'expose aucune date : sans cette table, impossible de savoir si
     * l'athlète a déjà répondu pour la campagne de septembre en cours.
     */
    public function up(): void
    {
        Schema::create('annual_questionnaire_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('submitted_at');
            // 1er septembre de la campagne à laquelle la réponse se rattache.
            $table->date('period_start');
            $table->timestamps();

            $table->index(['user_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_questionnaire_submissions');
    }
};
