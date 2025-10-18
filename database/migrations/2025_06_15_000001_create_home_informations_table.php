<?php
// database/migrations/2025_06_15_000001_create_home_informations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeInformationsTable extends Migration
{
    public function up()
    {
        Schema::create('home_informations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('video_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_informations');
    }
}
