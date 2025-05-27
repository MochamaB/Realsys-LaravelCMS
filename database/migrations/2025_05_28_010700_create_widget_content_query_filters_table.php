<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetContentQueryFiltersTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget_content_query_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('query_id');
            $table->unsignedBigInteger('field_id')->nullable();
            $table->string('field_key')->nullable();
            $table->string('operator'); // equals, not_equals, contains, greater_than, etc.
            $table->text('value')->nullable();
            $table->string('condition_group')->nullable(); // For AND/OR grouping
            $table->timestamps();
            
            $table->foreign('query_id')
                  ->references('id')
                  ->on('widget_content_queries')
                  ->onDelete('cascade');
            $table->foreign('field_id')
                  ->references('id')
                  ->on('content_type_fields')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widget_content_query_filters');
    }
}
