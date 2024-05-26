<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks_downloads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('case_id', 25);
            $table->string('state', 25);
            $table->integer('try');
            $table->integer('time');
            $table->tinyInteger('from')->nullable()->default('0')->comment('0: S3; 1: AsiaFtp');
            $table->tinyInteger('has_mapping_name')->nullable()->default(null)->comment('0: no,1: yes');

            $table->unique(["case_id"]);
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
       Schema::dropIfExists('tasks_downloads');
     }
}
