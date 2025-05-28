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
        Schema::table('templates', function (Blueprint $table) {
            // Add settings field to store template-specific configuration
            $table->json('settings')->nullable()->after('thumbnail_path');
            
            // Add is_default flag to mark a template as the default for its theme
            $table->boolean('is_default')->default(false)->after('is_active');
            
            // Ensure only one template per theme can be default
            // We'll handle this with application logic rather than a database constraint
            // as MySQL doesn't support conditional unique indexes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn(['settings', 'is_default']);
        });
    }
};
