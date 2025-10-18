<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mental_preparations', function (Blueprint $table) {
            // Ajouter le champ program_type (par défaut free)
            $table->enum('program_type', ['free', 'premium', 'paid'])->default('free')->after('status');

            // Ajouter le champ price
            $table->decimal('price', 8, 2)->nullable()->after('program_type');
        });
    }

    public function down()
    {
        Schema::table('mental_preparations', function (Blueprint $table) {
            $table->dropColumn(['program_type', 'price']);
        });
    }
};