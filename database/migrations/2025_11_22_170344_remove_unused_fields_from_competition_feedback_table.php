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
            $table->dropColumn([
                'difficulty_level',
                'negative_focus',
                'stress_tension',
                'competition_entry'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_feedbacks', function (Blueprint $table) {
            $table->decimal('difficulty_level', 3, 1)->after('victory_response');
            $table->decimal('negative_focus', 3, 1)->after('focus');
            $table->decimal('stress_tension', 3, 1)->after('emotional_stability');
            $table->decimal('competition_entry', 3, 1)->after('decision_making');
        });
    }
};
