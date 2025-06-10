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
        Schema::create('profile_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'Party Member', 'Executive', 'Aspirant', 'Voter', 'Admin'
            $table->string('code')->unique(); // Short code for reference
            $table->text('description')->nullable();
            $table->string('dashboard_route')->nullable(); // Route to redirect to after login
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_types');
    }
};
