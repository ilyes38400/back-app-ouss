<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['technique', 'tactique', 'physique', 'mental']);
            $table->string('title');
            $table->string('criterium')->nullable();  // critère de réussite
            $table->boolean('is_achieved')->default(false);
            $table->string('period', 7);              // "2026-07"
            $table->timestamps();

            $table->index(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_objectives');
    }
};
