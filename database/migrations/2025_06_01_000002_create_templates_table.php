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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('file_path');
            // Make slug unique per theme, not globally unique
            $table->unique(['theme_id', 'slug']);
            // No image URL fields as we're using Spatie Media Library
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->json('layout_data')->nullable(); // optional, for designer state
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
