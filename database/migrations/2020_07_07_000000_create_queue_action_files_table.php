<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueueActionFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_action_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source')->nullable()->default(null);
            $table->string('destination')->nullable()->default(null);
            $table->string('action', 50)->nullable()->default(null)->comment('copy,move,delete');
            $table->string('type', 50)->nullable()->default(null)->comment('working_checking');
            $table->string('status', 50)->nullable()->default(null)->comment('new,queue,finish,error,immediate');
            $table->integer('prodtool_task_id')->nullable()->default(null);
            $table->string('prodtool_case_id', 20)->nullable()->default(null);
            $table->integer('prodtool_user_id')->nullable()->default(null);
            $table->string('prodtool_user_name', 50)->nullable()->default(null);
            $table->integer('prodtool_staff_job_id')->nullable()->default(null);
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
       Schema::dropIfExists('queue_action_files');
     }
}
