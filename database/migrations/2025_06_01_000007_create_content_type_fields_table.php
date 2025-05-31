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
        Schema::create('content_type_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('field_type'); // text, rich_text, image, etc.
            $table->text('validation_rules')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->integer('position')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add unique constraint for slug within a content type
            $table->unique(['content_type_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_type_fields');
    }
};
