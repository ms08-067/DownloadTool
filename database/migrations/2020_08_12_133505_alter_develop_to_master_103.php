<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster103 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	/**Add a new column to hold the xml title contents for the RC message when ready unzipped .*/
        Schema::table('tasks_uploads', function (Blueprint $table) {
            $table->tinyInteger('move_to_jobfolder')->default('0')->after('initiator')->comment('status of move_to_jobfolder 0: not_moving; 1: moving, 2: moved, 3: notified');
            $table->tinyInteger('move_to_jobfolder_tries')->default('0')->after('initiator')->comment('status of move_to_jobfolder tries');

            $table->tinyInteger('sending_to_s3')->default('0')->after('initiator')->comment('status of sending_to_s3 0: not_moving; 1: ready to send 2: moving, 3: moved');
            $table->tinyInteger('sending_to_s3_tries')->default('0')->after('initiator')->comment('status of sending_to_s3 tries');

            $table->integer('pid')->nullable()->default(null)->comment('process id running, to use to stop the process if uplading again to same case ID ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	/***/
    	/***/
    }
}
