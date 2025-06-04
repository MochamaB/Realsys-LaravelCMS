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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->references('id')->on('menu_items')->onDelete('cascade');
            $table->string('label');
            $table->string('link_type')->default('url')->comment('url, page, section, external');
            $table->string('url')->nullable();
            $table->string('target')->nullable()->comment('_self, _blank, etc.');
            $table->foreignId('page_id')->nullable()->references('id')->on('pages')->nullOnDelete();
            $table->string('section_id')->nullable()->comment('ID of the section for one-page navigation');
            $table->json('visibility_conditions')->nullable()->comment('JSON with page/template/auth conditions');
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index for better performance
            $table->index(['menu_id', 'parent_id', 'is_active', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
