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
        Schema::create('polling_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 15)->unique();
            $table->foreignId('ward_id')->constrained()->onDelete('cascade');
            $table->string('location_description')->nullable();
            $table->unique(['name', 'ward_id']); // Polling station names unique within a ward
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polling_stations');
    }
};
