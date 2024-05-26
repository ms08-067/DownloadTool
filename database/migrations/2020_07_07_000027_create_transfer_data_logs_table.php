<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransferDataLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_data_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->default(null);
            $table->integer('task_id')->nullable()->default(null);
            $table->string('case_id', 20)->nullable()->default(null);
            $table->integer('staff_job_id')->nullable()->default(null);
            $table->string('action')->nullable()->default(null)->comment('new_working, working_checking, checking_redo, checking_multicontinue, redo_checking, unzip, ...');
            $table->integer('total_file')->nullable()->default(null);
            $table->float('total_data')->nullable()->default(null);
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
       Schema::dropIfExists('transfer_data_logs');
     }
}
