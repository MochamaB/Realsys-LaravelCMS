<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTypeFieldsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_type_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id');
            $table->string('name');
            $table->string('key');
            $table->string('type'); // text, textarea, rich_text, image, file, date, etc.
            $table->boolean('required')->default(false);
            $table->text('description')->nullable();
            $table->text('validation_rules')->nullable();
            $table->text('default_value')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
            
            $table->unique(['content_type_id', 'key']);
            $table->foreign('content_type_id')
                  ->references('id')
                  ->on('content_types')
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
        Schema::dropIfExists('content_type_fields');
    }
}