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
        Schema::table('user_workout_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('workout_type_id')->nullable();
            $table->foreign('workout_type_id')->references('id')->on('app_workout_types');
            $table->index('workout_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_workout_logs', function (Blueprint $table) {
            $table->dropForeign(['workout_type_id']);
            $table->dropIndex(['workout_type_id']);
            $table->dropColumn('workout_type_id');
        });
    }
};
