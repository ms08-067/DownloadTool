<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksUploadsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks_uploads_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('case_id', 25);
            $table->string('live');
            $table->string('state', 25);
            $table->integer('time');
            $table->string('url');
            $table->string('local');
            $table->bigInteger('size');
            $table->tinyInteger('zip')->default('0');
            $table->tinyInteger('zip_checks')->default('0')->comment('0: Not checked; 1: Checks OK');
            $table->tinyInteger('unzip_tries')->default('0')->comment('amount of times checking the new zip can be unzipped via unzip -t;');
            $table->tinyInteger('unzip_checks')->default('0')->comment('0: Not checked; 1: Checks OK');
            $table->tinyInteger('upload_attempt')->default('0')->comment('0: not uploaded; 1++: uploaded before');
            $table->string('type', 25);
            $table->tinyInteger('from')->nullable()->default('0')->comment('0: S3; 1: AsiaFtp');
            $table->tinyInteger('has_mapping_name')->nullable()->default(null)->comment('0: no,1: yes');
            $table->integer('pid')->nullable()->default(null);

            $table->unique(["case_id", "live"]);
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('tasks_uploads_files');
     }
}
