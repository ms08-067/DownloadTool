<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster115 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**Add a new column to hold the hashtag .*/
        Schema::table('tasks_downloads', function (Blueprint $table) {
            $table->tinyInteger('archived_case')->default(1)->after('updated_at')->comment('1: in jobfolder 2: moved to archive folder');
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
