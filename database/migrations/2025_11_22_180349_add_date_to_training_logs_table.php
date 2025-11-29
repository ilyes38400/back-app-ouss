<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            $table->date('date')->nullable()->after('duration');
        });

        // Set default date to created_at date for existing records
        DB::statement('UPDATE training_logs SET date = DATE(created_at) WHERE date IS NULL');

        // Now make the column required
        Schema::table('training_logs', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
};
