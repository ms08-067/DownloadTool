<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBr24DonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('br24dones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_pic_id');
            $table->unsignedInteger('task_id');
            $table->string('newpath')->comment('path in ready copy checking');
            $table->string('path_psd')->nullable()->default(null);
            $table->string('ready_path')->nullable()->default(null);
            $table->unsignedInteger('dirpath_id')->nullable()->default(null);
            $table->unsignedSmallInteger('work_time')->nullable()->default(null);
            $table->unsignedSmallInteger('work_time_input')->nullable()->default(null);
            $table->unsignedTinyInteger('rating')->nullable()->default('0');
            $table->unsignedInteger('qc_id');
            $table->unsignedInteger('worker_id');
            $table->tinyInteger('is_path')->default('0')->comment('1:have picture out,0:no picture output');
            $table->unsignedTinyInteger('is_ready')->default('0');
            $table->unsignedTinyInteger('is_money')->default('1');
            $table->string('note')->nullable()->default(null);
            $table->integer('reason_redo')->nullable()->default(null);
            $table->unsignedInteger('intern_redo_id')->nullable()->default(null);
            $table->unsignedInteger('extern_redo_id')->nullable()->default(null);
            $table->unsignedInteger('deleted')->default('0');
            $table->unsignedInteger('finished')->nullable()->default(null);
            $table->unsignedInteger('output_id')->nullable()->default(null);
            $table->integer('qc_ready_id')->nullable()->default(null);
            $table->unsignedTinyInteger('is_continue')->default('0')->comment('1: continue, 0: normal');
            $table->unsignedInteger('job_session_id');
            $table->unsignedTinyInteger('keep_pic')->default('0');
            $table->unsignedInteger('sqc_id')->nullable()->default(null);
            $table->unsignedInteger('parent_dir_id')->nullable()->default(null);
            $table->unsignedInteger('assign_time_id')->nullable()->default(null);
            $table->integer('staff_job_id')->nullable()->default(null);
            $table->unsignedInteger('remove_job')->nullable()->default(null);
            $table->dateTime('created');
            $table->dateTime('modified');
            $table->string('extension', 128);
            $table->string('path_no_extension');
            $table->tinyInteger('is_update');
            $table->tinyInteger('is_otto_sub_task')->nullable()->default('0')->comment('1: is_otto_sub_task; 0: normal');
            $table->tinyInteger('is_otto_sub_task_upload')->nullable()->default(null);

            $table->index(["dirpath_id"]);

            $table->index(["task_id"]);

            $table->index(["staff_pic_id"]);

            $table->index(["staff_job_id"]);

            $table->index(["qc_id"]);

            $table->index(["intern_redo_id"]);

            $table->index(["worker_id"]);

            $table->index(["extern_redo_id"]);

            $table->index(["ready_path"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('br24dones');
     }
}
