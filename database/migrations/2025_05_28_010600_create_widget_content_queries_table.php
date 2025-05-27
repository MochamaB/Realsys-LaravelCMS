<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetContentQueriesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget_content_queries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id')->nullable();
            $table->integer('limit')->nullable();
            $table->integer('offset')->default(0);
            $table->string('order_by')->nullable();
            $table->enum('order_direction', ['asc', 'desc'])->default('desc');
            $table->timestamps();
            
            $table->foreign('content_type_id')
                  ->references('id')
                  ->on('content_types')
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
        Schema::dropIfExists('widget_content_queries');
    }
}
