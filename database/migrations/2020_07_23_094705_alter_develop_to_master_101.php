<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster101 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	/**Add a new column to hold the xml title contents for the RC message when ready unzipped .*/
        Schema::table('tasks_downloads_files', function (Blueprint $table) {
            $table->string('xml_jobid_title')->default('')->after('pid')->comment('showing parent jobid and M or R');
            $table->string('unzip_checks_tries')->default('0')->after('xml_title_contents')->comment('amount of times to try checking extract folder file count with file count of zip');
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
