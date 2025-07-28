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
              // GridStack Section Positioning
            $table->integer('grid_x')->default(0);
            $table->integer('grid_y')->default(0);
            $table->integer('grid_w')->default(12);
            $table->integer('grid_h')->default(4);
            $table->string('grid_id')->unique();
            $table->json('grid_config')->nullable();
            $table->boolean('allows_widgets')->default(true);
            $table->json('widget_types')->nullable();
            $table->string('column_span_override')->nullable();
            $table->string('column_offset_override')->nullable();
            $table->string('css_classes')->nullable();
            $table->string('background_color')->nullable();
            // No background_image field as we're using Spatie Media Library
            $table->json('padding')->nullable();
            $table->json('margin')->nullable();
            $table->boolean('locked_position')->default(false);
            $table->json('resize_handles')->nullable();
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
