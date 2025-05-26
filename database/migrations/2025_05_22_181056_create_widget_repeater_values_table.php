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
        Schema::create('widget_repeater_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_repeater_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('widget_type_field_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('widget_repeater_group_id');
            $table->index('widget_type_field_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_repeater_values');
    }
};
