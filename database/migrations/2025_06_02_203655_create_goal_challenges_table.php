<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('goal_challenges', function (Blueprint $table) {
            $table->id();
            $table->enum('theme', ['physique','alimentaire','mental']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->enum('status',['active','inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_challenges');
    }
};
