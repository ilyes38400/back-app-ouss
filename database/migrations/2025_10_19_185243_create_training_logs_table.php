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
        Schema::create('training_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('ec_customers')->onDelete('cascade');
            $table->string('discipline');
            $table->enum('dominance', ['mental', 'physique', 'technique', 'tactique']);
            $table->string('duration', 50);

            // Scores de 0 à 10
            $table->decimal('intensity', 3, 1);
            $table->decimal('ifp', 3, 1); // Indice de Fatigue Physique
            $table->decimal('engagement', 3, 1);
            $table->decimal('focus', 3, 1);
            $table->decimal('stress', 3, 1);

            // Champs optionnels
            $table->text('comment')->nullable();
            $table->boolean('productive')->default(true);

            $table->timestamps();

            // Index
            $table->index(['user_id', 'created_at']);
            $table->index('productive');
            $table->index('dominance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_logs');
    }
};
