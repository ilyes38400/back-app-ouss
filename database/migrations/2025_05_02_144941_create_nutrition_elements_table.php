<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/2025_04_23_000000_create_nutrition_elements_table.php
public function up()
{
    Schema::create('nutrition_elements', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();        // ex. "prise_de_masse"
        $table->text('description')->nullable(); // champ TinyMCE
        $table->string('status')->default('active');
        $table->timestamps();
    });
}

};
