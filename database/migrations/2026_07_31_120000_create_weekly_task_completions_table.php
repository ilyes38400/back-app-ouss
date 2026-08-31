<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * État coché/décoché des tâches de la semaine.
     *
     * Certaines tâches sont déduites des données (questionnaire rempli, séance
     * enregistrée) : une ligne ici est un choix explicite de l'athlète et prend
     * le pas sur l'état calculé.
     */
    public function up(): void
    {
        Schema::create('weekly_task_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('task_key', 64);
            $table->date('week_start');
            $table->boolean('is_done')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'task_key', 'week_start'], 'weekly_task_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_task_completions');
    }
};
