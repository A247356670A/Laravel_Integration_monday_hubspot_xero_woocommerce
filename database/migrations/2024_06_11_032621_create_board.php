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
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('board_name')->nullable();
            $table->text('board_id');
            $table->text('webhookUrl_test')->nullable();
            $table->text('webhookUrl_mapping')->nullable();
            $table->text('webhookUrl_woocommerce')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board');
    }
};
