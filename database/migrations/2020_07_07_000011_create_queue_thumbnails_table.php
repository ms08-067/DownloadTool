<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueueThumbnailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_thumbnails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path')->nullable()->default(null);
            $table->string('destination')->nullable()->default(null);
            $table->double('size')->nullable()->default(null)->comment('byte');
            $table->string('extension', 50)->nullable()->default(null);
            $table->string('status', 50)->nullable()->default(null)->comment('new,queue,finish,error');
            $table->string('prodtool_type', 50)->nullable()->default(null)->comment('example,job');
            $table->integer('prodtool_task_id')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('queue_thumbnails');
     }
}
