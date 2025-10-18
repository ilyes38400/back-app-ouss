<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMentalPreparationsTable extends Migration
{
    public function up()
    {
        Schema::create('mental_preparations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Plus d'->after() ici :
            $table->enum('video_type', ['upload_video', 'external_url'])
                  ->nullable();
            $table->string('video_url')->nullable();

            $table->enum('status', ['active', 'inactive'])
                  ->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mental_preparations');
    }
}
