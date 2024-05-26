<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster133 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**Add a new column to hold the xml title contents for the RC message when ready unzipped .*/

        //Schema::dropIfExists('v_download_files');
        $drop = "DROP VIEW v_download_files;";
        DB::unprepared($drop);

        $view = "
            CREATE VIEW `v_download_files` 
            AS
            SELECT 
                tdn.*,
                attt_subq.xml_deliverytime_contents_formatted,
                apusr.fullname AS last_updated_by_name,
                attt_subq.xml_title_contents,
                attt_subq.xml_jobid_title,
                attt_subq.xml_tool_client,
                REPLACE(attt_subq.xml_jobinfo, '<br>', '[*~*]') AS xml_jobinfo,
                REPLACE(attt_subq.xml_jobinfoproduction, '<br>', '[*~*]') AS xml_jobinfoproduction,

                COALESCE(DATETIME(tdn.custom_delivery_time), DATETIME(attt_subq.xml_deliverytime_contents_formatted_sub_2), DATETIME(vuf.created_at)) AS expected_delivery_date_coalesce,
                
                DATETIME(tdn.custom_delivery_time) AS custom_delivery_time_original,
                DATETIME(attt_subq.xml_deliverytime_contents_formatted) AS xml_deliverytime_contents_formatted_original,
                DATETIME(attt_subq.xml_deliverytime_contents_formatted_sub_2) AS xml_deliverytime_contents_formatted_sub_2_original,

                COALESCE(vuf.state, tdn.state) AS status_of_case,

                vuf.state AS vuf_state,
                vuf.initiator AS vuf_initiator,
                vuf.updated_at AS vuf_updated_at,
                vuf.created_at AS vuf_created_at,
                vuf.move_to_jobfolder AS vuf_move_to_jobfolder,
                vuf.move_to_jobfolder_tries AS vuf_move_to_jobfolder_tries,
                vuf.sending_to_s3 AS vuf_sending_to_s3,
                vuf.sending_to_s3_tries AS vuf_sending_to_s3_tries,
                vuf.pid AS vuf_pid,
                vuf.custom_output_real AS vuf_custom_output_real
            FROM
                tasks_downloads tdn
                    LEFT JOIN
                (SELECT 
                    tdf.*,
                    strftime('%Y-%m-%d %H:%M:%S', datetime(tdf.xml_deliverytime_contents, 'unixepoch')) AS xml_deliverytime_contents_formatted,
                    strftime('%Y-%m-%d %H:%M:%S', datetime(tdf.xml_deliverytime_contents, 'unixepoch', '-2 hours')) AS xml_deliverytime_contents_formatted_sub_2
                FROM
                    tasks_downloads_files tdf
                GROUP BY tdf.case_id) AS attt_subq ON tdn.case_id = attt_subq.case_id
                    LEFT JOIN
                employees apusr ON tdn.last_updated_by = apusr.user_id
                    LEFT JOIN
                v_upload_files vuf ON tdn.case_id = vuf.case_id
            ORDER BY expected_delivery_date_coalesce DESC;
            ";
            /** order by delivery date .. */
            /** if the custom delivery date is not null that should take precedence over the xml delivery date */
            /** if both the xml delivery date and the custom delivery date is null then these must be put in the front? */


        DB::unprepared($view);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v_download_files');
        /***/
    }
}
