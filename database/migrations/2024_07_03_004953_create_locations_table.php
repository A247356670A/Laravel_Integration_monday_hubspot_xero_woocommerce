<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('Address')->nullable();
            $table->text('Latitude')->nullable();
            $table->text('Longitude')->nullable();
            $table->text('Country_long')->nullable();
            $table->text('Country_short')->nullable();
            $table->text('City_long')->nullable();
            $table->text('City_short')->nullable();
            $table->text('Street_long')->nullable();
            $table->text('Street_short')->nullable();
            $table->text('StreetNum_long')->nullable();
            $table->text('StreetNum_Short')->nullable();
            $table->string('placeId', 128)->unique()->nullable();
            $table->text('itemId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
