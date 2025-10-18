<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('program_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('program_id');
            $table->enum('program_type', ['workout', 'mental']); // Type de programme
            $table->string('program_title');
            $table->json('program_data')->nullable(); // Données du programme au moment de l'achat
            $table->string('purchase_platform'); // 'apple', 'google', 'stripe', etc.
            $table->string('platform_transaction_id');
            $table->string('platform_product_id');
            $table->json('receipt_data')->nullable(); // Données du reçu
            $table->timestamp('purchase_date');
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['active', 'expired', 'refunded'])->default('active');
            $table->timestamps();

            // Index
            $table->foreign('user_id')->references('id')->on('ec_customers')->onDelete('cascade');
            $table->index(['user_id', 'program_id', 'program_type']);
            $table->index(['platform_transaction_id']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('program_purchases');
    }
};