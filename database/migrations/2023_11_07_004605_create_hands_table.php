<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('round_id');
            $table->unsignedBigInteger('gameplayer_id');
            $table->timestamps();

            $table->foreign('round_id')->references('id')->on('rounds')->OnDelete('cascade');
            $table->foreign('gameplayer_id')->references('id')->on('game_player')->OnDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardhands');
        Schema::dropIfExists('hands');
    }
};
