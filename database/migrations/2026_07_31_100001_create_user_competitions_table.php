<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_competitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->date('competition_date');
            $table->string('location')->nullable();
            // Critères de réussite saisis par l'athlète, affichés en tags
            // ex: ["Activation", "Lâcher prise"]
            $table->json('focus_tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'competition_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_competitions');
    }
};
