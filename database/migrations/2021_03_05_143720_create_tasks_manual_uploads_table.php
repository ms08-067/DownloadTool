<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksManualUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks_manual_uploads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('case_id', 25);
            $table->string('state', 25);
            $table->integer('try');
            $table->integer('time');
            $table->tinyInteger('from')->nullable()->default('0')->comment('0: DowloadUploadServer; 1: s3');
            $table->tinyInteger('has_mapping_name')->nullable()->default(null)->comment('0: no; 1: yes');
            $table->mediumText('initiator')->nullable()->default(null);
            
            $table->unique(["case_id"]);
            $table->timestamps();

            $table->tinyInteger('move_to_jobfolder')->default('0')->after('initiator')->comment('status of move_to_jobfolder 0: not_moving; 1: moving, 2: moved, 3: notified');
            $table->tinyInteger('move_to_jobfolder_tries')->default('0')->after('initiator')->comment('status of move_to_jobfolder tries');

            $table->tinyInteger('sending_to_s3')->default('0')->after('initiator')->comment('status of sending_to_s3 0: not_moving; 1: ready to send 2: moving, 3: moved');
            $table->tinyInteger('sending_to_s3_tries')->default('0')->after('initiator')->comment('status of sending_to_s3 tries');

            $table->integer('pid')->nullable()->default(null)->comment('process id running, to use to stop the process if uplading again to same case ID ');
            $table->string('custom_output_real')->nullable()->default(null)->after('pid')->comment('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('tasks_manual_uploads');
     }
}
