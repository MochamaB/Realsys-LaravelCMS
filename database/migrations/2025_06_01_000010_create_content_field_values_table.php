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
        Schema::create('content_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_type_field_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add unique constraint for field values to prevent duplicates
            $table->unique(['content_item_id', 'content_type_field_id'], 'content_field_value_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_field_values');
    }
};
