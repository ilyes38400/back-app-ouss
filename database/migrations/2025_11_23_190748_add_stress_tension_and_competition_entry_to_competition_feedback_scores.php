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
            $table->decimal('stress_tension', 3, 1)->default(5.0);
            $table->decimal('competition_entry', 3, 1)->default(5.0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_feedbacks', function (Blueprint $table) {
            $table->dropColumn(['stress_tension', 'competition_entry']);
        });
    }
};
