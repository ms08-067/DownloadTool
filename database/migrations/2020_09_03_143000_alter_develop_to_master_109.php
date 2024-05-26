<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster109 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	/**Add a new column to hold the xml delivery time and adjusted delivery time .*/
        Schema::create('scheduled_tasks_toggle', function (Blueprint $table) {
            $table->enum('active', ['1', '2'])->default('1')->comment('1: active 2: inactive');
        });

        $data = [
            [
                'active' => '1',
            ],
        ];

        DB::table('scheduled_tasks_toggle')->delete();
        DB::table('scheduled_tasks_toggle')->insert($data);

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
