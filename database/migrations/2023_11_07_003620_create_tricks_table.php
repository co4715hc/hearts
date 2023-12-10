<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTricksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tricks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('round_id');
            $table->timestamps();

            $table->foreign('round_id')->references('id')->on('rounds')->OnDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tricks');
    }
};
