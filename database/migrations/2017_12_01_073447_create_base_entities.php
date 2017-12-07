<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaseEntities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('channel_id');
            $table->bigInteger('stream_id');
            $table->bigInteger('game_id')->unsigned();
            $table->integer('service_id')->unsigned();
            $table->bigInteger('viewer_count');
            $table->boolean('active')->default(false);
            $table->unique(['stream_id', 'game_id']);
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
        });

        Schema::create('streaming_services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->unique();
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->foreign('game_id')
                  ->references('id')->on('games');
            $table->foreign('service_id')
                  ->references('id')->on('streaming_services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streams');
        Schema::dropIfExists('games');
        Schema::dropIfExists('streaming_services');
    }
}
