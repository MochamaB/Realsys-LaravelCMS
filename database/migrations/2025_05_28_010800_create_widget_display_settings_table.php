<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetDisplaySettingsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget_display_settings', function (Blueprint $table) {
            $table->id();
            $table->string('layout')->nullable();
            $table->string('view_mode')->nullable();
            $table->string('pagination_type')->nullable();
            $table->integer('items_per_page')->nullable();
            $table->string('empty_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widget_display_settings');
    }
}
