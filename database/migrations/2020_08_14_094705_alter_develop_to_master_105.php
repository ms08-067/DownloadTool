<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster105 extends Migration
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
            $table->string('xml_deliverytime_contents')->nullable()->default(null)->after('updated_at')->comment('xml delivery_time column');
            $table->string('file_count')->nullable()->default(null)->after('updated_at')->comment('number of files in the zip');
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
