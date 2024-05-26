<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOocvScanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oocv_scan', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('try')->nullable()->default('1');
            $table->string('live', 128);
            $table->string('local');
            $table->integer('size');
            $table->string('state', 25);
            $table->string('case_id', 25);
            $table->string('task_id', 25);
            $table->string('project_id', 25);
            $table->string('name')->nullable()->default(null);
            $table->string('type', 25)->nullable()->default(null);

            $table->index(["live"], 'live');
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
       Schema::dropIfExists('oocv_scan');
     }
}
