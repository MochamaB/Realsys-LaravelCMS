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
        Schema::create('form_submission_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('form_field_id')->nullable()->constrained()->onDelete('set null');
            $table->string('field_name'); // Store the field name in case the field is deleted
            $table->text('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('form_submission_id');
            $table->index('form_field_id');
            $table->index('field_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submission_values');
    }
};
