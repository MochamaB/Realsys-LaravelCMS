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
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('media_folders')->onDelete('cascade');
            $table->timestamps();
        });
        
        // Add folder_id to media table if it doesn't exist
        if (!Schema::hasColumn('media', 'folder_id')) {
            Schema::table('media', function (Blueprint $table) {
                $table->unsignedBigInteger('folder_id')->nullable()->after('model_id');
                $table->foreign('folder_id')->references('id')->on('media_folders')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove folder_id from media table
        if (Schema::hasColumn('media', 'folder_id')) {
            Schema::table('media', function (Blueprint $table) {
                $table->dropForeign(['folder_id']);
                $table->dropColumn('folder_id');
            });
        }
        
        Schema::dropIfExists('media_folders');
    }
};
