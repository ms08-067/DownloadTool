<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster120 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	/**Add a new column to hold the xml title contents for the RC message when ready unzipped .*/

        // //Schema::dropIfExists('v_manual_upload_files');
        // $drop = "DROP VIEW v_manual_upload_files;";
        // DB::unprepared($drop);

        $view = "
            CREATE VIEW `v_manual_upload_files`
            AS
            SELECT 
                tup.*,
                attt_subq.xml_title_contents,
                attt_subq.xml_jobid_title
            FROM
                tasks_manual_uploads tup
                    LEFT JOIN
                (SELECT 
                    tdf.*
                FROM
                    tasks_manual_downloads_files tdf
                GROUP BY tdf.case_id) AS attt_subq ON tup.case_id = attt_subq.case_id;
            ";
        DB::unprepared($view);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v_manual_upload_files');
    	/***/
    }
}
