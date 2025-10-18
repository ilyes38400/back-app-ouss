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
        Schema::table('app_workouts', function (Blueprint $table) {
            $table->boolean('is_monthly_program')->default(false);
            $table->index('is_monthly_program');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_workouts', function (Blueprint $table) {
            $table->dropIndex(['is_monthly_program']);
            $table->dropColumn('is_monthly_program');
        });
    }
};
