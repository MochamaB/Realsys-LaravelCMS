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
        Schema::create('mobile_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'Safaricom', 'Airtel', 'Telkom', etc.
            $table->string('prefix')->nullable(); // e.g., '07xx' for identification
            $table->string('logo_path')->nullable(); // Path to provider logo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_providers');
    }
};
