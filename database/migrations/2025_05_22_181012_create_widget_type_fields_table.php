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
        Schema::create('widget_type_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_type_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('label');
            $table->string('field_type');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_repeatable')->default(false);
            $table->string('validation_rules')->nullable();
            $table->text('help_text')->nullable();
            $table->string('default_value')->nullable();
            $table->integer('order_index')->default(0);
            $table->softDeletes();
            $table->timestamps();
            
            // Add index for performance
            $table->index('widget_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_type_fields');
    }
};
