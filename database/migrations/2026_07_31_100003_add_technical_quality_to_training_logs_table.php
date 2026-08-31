<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le slider "Qualité technique" existait déjà côté app mais n'était pas persisté.
     * Il est nécessaire pour la composante Momentum du score "Prêt à performer".
     */
    public function up(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            $table->decimal('technical_quality', 3, 1)->nullable()->after('focus');
        });
    }

    public function down(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            $table->dropColumn('technical_quality');
        });
    }
};
