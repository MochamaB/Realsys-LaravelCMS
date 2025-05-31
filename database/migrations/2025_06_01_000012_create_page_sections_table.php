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
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_section_id')->constrained()->onDelete('restrict');
            $table->integer('position')->default(0);
            $table->string('column_span_override')->nullable();
            $table->string('column_offset_override')->nullable();
            $table->string('css_classes')->nullable();
            $table->string('background_color')->nullable();
            // No background_image field as we're using Spatie Media Library
            $table->json('padding')->nullable();
            $table->json('margin')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
