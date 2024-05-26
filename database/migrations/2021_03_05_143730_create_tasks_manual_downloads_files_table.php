<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksManualDownloadsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks_manual_downloads_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('case_id', 25);
            $table->string('live');
            $table->string('state', 25);
            $table->integer('time');
            $table->string('url');
            $table->string('local');
            $table->bigInteger('size');
            $table->tinyInteger('unzip')->default('0');
            $table->tinyInteger('unzip_tries')->default('0')->comment('amount of times checking the zip can be unzipped via unzip -t;');
            $table->tinyInteger('unzip_checks')->default('0')->comment('0: Not checked; 1: Checks OK');
            $table->string('type', 25);
            $table->tinyInteger('from')->nullable()->default('0')->comment('0: S3; 1: AsiaFtp');
            $table->tinyInteger('has_mapping_name')->nullable()->default(null)->comment('0: no,1: yes');
            $table->integer('pid')->nullable()->default(null);

            $table->nullableTimestamps();

            $table->string('xml_title_contents')->default('');
            $table->string('xml_jobid_title')->default('')->comment('showing parent jobid and M or R');
            $table->string('unzip_checks_tries')->default('0')->comment('amount of times to try checking extract folder file count with file count of zip');
            $table->string('xml_deliverytime_contents')->nullable()->default(null)->comment('xml delivery_time column');
            $table->string('file_count')->nullable()->default(null)->comment('number of files in the zip');            
            $table->text('xml_jobinfo')->default('')->comment('showing jobInfo');
            $table->text('xml_jobinfoproduction')->default('')->comment('showing jobInfo');

            $table->unique(["case_id", "live"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('tasks_manual_downloads_files');
     }
}
