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
        Schema::create('page_section_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_section_id')->constrained()->onDelete('cascade');
            $table->foreignId('widget_id')->constrained()->onDelete('restrict');
            $table->integer('position')->default(0);
            $table->integer('grid_x')->default(0);
            $table->integer('grid_y')->default(0);
            $table->integer('grid_w')->default(12);
            $table->integer('grid_h')->default(4);
            $table->string('grid_id')->unique();
            $table->string('column_position')->nullable(); // left, right, full
            $table->json('settings')->nullable();
            $table->json('content_query')->nullable();
            $table->string('css_classes')->nullable();
            $table->json('padding')->nullable();
            $table->json('margin')->nullable();
            $table->integer('min_height')->nullable();
            $table->integer('max_height')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_section_widgets');
    }
};
