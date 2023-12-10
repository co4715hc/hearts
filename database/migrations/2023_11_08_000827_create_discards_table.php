<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscardsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cardhand_id');
            $table->unsignedBigInteger('trick_id');
            $table->timestamps();

            $table->foreign('cardhand_id')->references('id')->on('cardhands')->OnDelete('cascade');
            $table->foreign('trick_id')->references('id')->on('tricks')->OnDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discards');
    }
};
