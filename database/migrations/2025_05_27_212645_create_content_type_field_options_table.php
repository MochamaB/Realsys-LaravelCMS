<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTypeFieldOptionsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_type_field_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('field_id');
            $table->string('label');
            $table->string('value');
            $table->integer('order_index')->default(0);
            $table->timestamps();
            
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
        Schema::dropIfExists('content_type_field_options');
    }
}