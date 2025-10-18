<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('app_user_profiles', function (Blueprint $table) {
            // Déclare ideal_weight comme VARCHAR (longueur 10 suffira généralement)
            $table->string('ideal_weight', 10)
                  ->nullable()
                  ->after('weight_unit');
        });
    }

    public function down()
    {
        Schema::table('app_user_profiles', function (Blueprint $table) {
            $table->dropColumn('ideal_weight');
        });
    }
};
