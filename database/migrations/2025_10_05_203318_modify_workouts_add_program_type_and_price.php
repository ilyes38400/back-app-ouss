<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('app_workouts', function (Blueprint $table) {
            // Ajouter le nouveau champ program_type
            $table->enum('program_type', ['free', 'premium', 'paid'])->default('free')->after('is_premium');

            // Ajouter le champ price
            $table->decimal('price', 8, 2)->nullable()->after('program_type');
        });

        // Migrer les données existantes
        DB::table('app_workouts')->where('is_premium', 1)->update(['program_type' => 'premium']);
        DB::table('app_workouts')->where('is_premium', 0)->update(['program_type' => 'free']);

        Schema::table('app_workouts', function (Blueprint $table) {
            // Supprimer l'ancien champ is_premium après migration
            $table->dropColumn('is_premium');
        });
    }

    public function down()
    {
        Schema::table('app_workouts', function (Blueprint $table) {
            // Remettre is_premium
            $table->integer('is_premium')->default(0)->after('level_id');
        });

        // Restaurer les données
        DB::table('app_workouts')->where('program_type', 'premium')->update(['is_premium' => 1]);
        DB::table('app_workouts')->where('program_type', 'free')->update(['is_premium' => 0]);

        Schema::table('app_workouts', function (Blueprint $table) {
            // Supprimer les nouveaux champs
            $table->dropColumn(['program_type', 'price']);
        });
    }
};