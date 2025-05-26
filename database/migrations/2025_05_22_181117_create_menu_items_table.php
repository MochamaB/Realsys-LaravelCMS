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
            $table->foreignId('parent_id')->nullable()->references('id')->on('menu_items')->onDelete('set null');
            $table->string('title');
            $table->string('link_type'); // enum: page, custom, etc.
            $table->foreignId('page_id')->nullable()->constrained()->onDelete('set null');
            $table->string('custom_url')->nullable();
            $table->string('target')->default('_self'); // _self, _blank, etc.
            $table->string('css_class')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('menu_id');
            $table->index('parent_id');
            $table->index('is_active');
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
