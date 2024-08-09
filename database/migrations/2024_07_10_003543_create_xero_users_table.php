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
        Schema::create('xero_users', function (Blueprint $table) {
            $table->id();
            $table->string('xero_id')->unique();
            $table->string('authEvent_id')->unique();
            $table->string('tenant_id')->unique();
            $table->string('tenant_type')->unique()->nullable();
            $table->string('tenant_name')->unique()->nullable();
            $table->string('app_name')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xero_users');
    }
};
