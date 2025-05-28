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
        Schema::table('template_sections', function (Blueprint $table) {
            // Add settings field to store section-specific configuration
            $table->json('settings')->nullable()->after('description');
            
            // Add type field to categorize sections (header, footer, sidebar, content, etc.)
            $table->string('type')->default('content')->after('settings');
            
            // Add width field to store layout information (e.g., col-md-8)
            $table->string('width')->nullable()->after('type');
            
            // Add a is_active field if it doesn't exist
            if (!Schema::hasColumn('template_sections', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('order_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_sections', function (Blueprint $table) {
            $table->dropColumn(['settings', 'type', 'width']);
            
            // Only drop is_active if we added it
            if (Schema::hasColumn('template_sections', 'is_active') && 
                !in_array('is_active', ['settings', 'type', 'width'])) {
                $table->dropColumn('is_active');
            }
        });
    }
};
