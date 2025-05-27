<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentFieldValuesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_item_id');
            $table->unsignedBigInteger('field_id');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->unique(['content_item_id', 'field_id']);
            $table->foreign('content_item_id')
                  ->references('id')
                  ->on('content_items')
                  ->onDelete('cascade');
            $table->foreign('field_id')
                  ->references('id')
                  ->on('content_type_fields')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_field_values');
    }
}