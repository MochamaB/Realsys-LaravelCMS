<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWidgetsTableForContentSystem extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->unsignedBigInteger('content_query_id')->nullable()->after('widget_type_id');
            $table->unsignedBigInteger('display_settings_id')->nullable()->after('content_query_id');
            
            $table->foreign('content_query_id')
                  ->references('id')
                  ->on('widget_content_queries')
                  ->onDelete('set null');
            
            $table->foreign('display_settings_id')
                  ->references('id')
                  ->on('widget_display_settings')
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
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropForeign(['content_query_id']);
            $table->dropForeign(['display_settings_id']);
            $table->dropColumn('content_query_id');
            $table->dropColumn('display_settings_id');
        });
    }
}
