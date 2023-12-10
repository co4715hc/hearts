<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardhandsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cardhands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hand_id');
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('from_hand_id')->nullable();
            $table->timestamps();

            $table->foreign('hand_id')->references('id')->on('hands')->OnDelete('cascade');
            $table->foreign('card_id')->references('id')->on('cards')->OnDelete('cascade');
            $table->foreign('from_hand_id')->references('id')->on('hands')->OnDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardhands');
    }
};
