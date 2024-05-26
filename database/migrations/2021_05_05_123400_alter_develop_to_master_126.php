<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster126 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	/**Add a new column to hold the xml delivery time and adjusted delivery time .*/
        Schema::create('queue_delay_seconds_manualdl', function (Blueprint $table) {
            $table->unsignedInteger('queue_delay_seconds')->default(1)->comment('manually bypass the queue_delay_seconds between each queue job');
        });

        $data = [
            [
                'queue_delay_seconds' => 1,
            ],
        ];

        DB::table('queue_delay_seconds_manualdl')->delete();
        DB::table('queue_delay_seconds_manualdl')->insert($data);

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
