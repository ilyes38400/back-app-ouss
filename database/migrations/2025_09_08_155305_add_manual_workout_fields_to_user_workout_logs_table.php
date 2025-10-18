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
            $table->enum('intensity_level', ['faible', 'modere', 'intense', 'tres_intense'])->nullable()->after('workout_type_id');
            $table->integer('duration_minutes')->nullable()->after('intensity_level');
            $table->boolean('is_manual_entry')->default(false)->after('duration_minutes');
            $table->text('notes')->nullable()->after('is_manual_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_workout_logs', function (Blueprint $table) {
            $table->dropColumn(['intensity_level', 'duration_minutes', 'is_manual_entry', 'notes']);
        });
    }
};
