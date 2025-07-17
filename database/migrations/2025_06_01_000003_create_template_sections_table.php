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
        Schema::create('template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->integer('position')->default(0);
            // Grid positioning columns for GridStack
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->integer('w')->default(12);
            $table->integer('h')->default(3);
            $table->string('section_type')->default('full-width'); // full-width, multi-column, etc.
            $table->string('column_layout')->nullable(); // 12, 6-6, 8-4, etc.
            $table->boolean('is_repeatable')->default(false);
            $table->integer('max_widgets')->nullable();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); // for nesting
            $table->foreign('parent_id')->references('id')->on('template_sections')->onDelete('cascade');
            $table->json('widget_data')->nullable(); // optional
            $table->timestamps();
            $table->softDeletes();
            
            // Add unique constraint for slug within a template
            $table->unique(['template_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_sections');
    }
};
