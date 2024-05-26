<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster122 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	/**Add a new column to hold the xml delivery time and adjusted delivery time .*/
        Schema::create('bypass_manualdl_filecountcheck_force_notify', function (Blueprint $table) {
            $table->unsignedInteger('case_id')->nullable()->default(NULL)->comment('manually bypass the case_id file amount count step of jobdirectory and unzipfolder and simply notify job is downloaded via rocketchat');
        });

        $data = [
            [
                'case_id' => NULL,
            ],
        ];

        DB::table('bypass_manualdl_filecountcheck_force_notify')->delete();
        DB::table('bypass_manualdl_filecountcheck_force_notify')->insert($data);

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
