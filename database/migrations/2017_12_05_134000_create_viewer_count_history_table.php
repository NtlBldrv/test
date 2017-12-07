<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewerCountHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viewer_count_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('viewer_count');
            $table->bigInteger('stream_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('viewer_count_history', function (Blueprint $table) {
            $table->foreign('stream_id')
                  ->references('id')->on('streams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viewer_count_history');
    }
}
