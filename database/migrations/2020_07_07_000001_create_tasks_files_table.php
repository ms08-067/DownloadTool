<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateTasksFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_id');
            $table->string('case_id', 25);
            $table->string('jobIdTitle', 50)->nullable()->default(null);
            $table->string('local');
            $table->string('live');
            $table->string('state', 25);
            $table->timestamp('time')->useCurrent();
            $table->string('file_name');
            $table->integer('size');
            $table->string('file_path');
            $table->string('file_path_original')->nullable()->default(null);
            $table->string('upload_id')->nullable()->default(null);
            $table->string('type', 25)->nullable()->default(null);
            $table->tinyInteger('order_upload')->nullable()->default('0');
            $table->string('j_key')->nullable()->default(null);
            $table->string('uuid')->nullable()->default(null);
            $table->tinyInteger('is_exists')->nullable()->default('1')->comment('1: file exists, 0: not exists');
            $table->tinyInteger('changed_split')->nullable()->default(null);
            $table->tinyInteger('error_number')->nullable()->default('0');
            $table->integer('pid')->nullable()->default(null);
            $table->string('folder', 128);
            $table->integer('customer_id')->nullable()->default(null);

            $table->index(["task_id"], 'task_id');

            $table->index(["type"], 'type');

            $table->index(["pid"], 'pid');

            $table->index(["task_id", "type"], 't1');

            $table->index(["state"], 'state');

            $table->index(["case_id"], 'case_id');

            $table->index(["local"], 'local');
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
       Schema::dropIfExists('tasks_files');
     }
}
