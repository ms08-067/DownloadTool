<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksManualDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks_manual_downloads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('case_id', 25);
            $table->string('state', 25);
            $table->integer('try');
            $table->integer('time');
            $table->tinyInteger('from')->nullable()->default('0')->comment('0: S3; 1: AsiaFtp');
            $table->tinyInteger('has_mapping_name')->nullable()->default(null)->comment('0: no,1: yes');

            $table->timestamps();

            $table->string('last_updated_by')->nullable()->default(null);
            $table->string('assignees')->nullable()->default(null)->comment('assignees column');
            $table->string('custom_delivery_time')->nullable()->default(null)->comment('custom delivery_time column');
            $table->string('custom_color')->nullable()->default('FFFFFF')->comment('custom color column');
            $table->string('custom_internal_notes')->nullable()->default(NULL)->comment('custom internal notes column');
            $table->unsignedInteger('custom_job_star_rating')->nullable()->default(NULL)->comment('custom job star rating column');
            $table->string('custom_job_star_rating_comment')->nullable()->default(NULL)->comment('custom job star rating comments column');
            $table->string('custom_hashtag')->nullable()->default(null)->comment('custom hashtag column');
            $table->tinyInteger('archived_case')->default(1)->comment('1: in jobfolder 2: moved to archive folder');
            $table->string('custom_output_expected')->nullable()->default(null)->comment('custom hashtag column');
            $table->tinyInteger('preview_req')->default(2)->comment('1: required to provide preview for customer 2: not required to provide preview for customer');
            $table->tinyInteger('created_xml')->default(2)->comment('1: tool created xml file 2: tool sourced xml file from amazon');
            
            $table->unique(["case_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('tasks_manual_downloads');
     }
}
