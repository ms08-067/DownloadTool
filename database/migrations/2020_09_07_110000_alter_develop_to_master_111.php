<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster111 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	/**Add a new column to hold the xml delivery time and adjusted delivery time .*/
        Schema::table('tasks_downloads', function (Blueprint $table) {
            $table->string('custom_internal_notes')->nullable()->default(NULL)->after('updated_at')->comment('custom internal notes column');
            $table->unsignedInteger('custom_job_star_rating')->nullable()->default(NULL)->after('updated_at')->comment('custom job star rating column');
            $table->string('custom_job_star_rating_comment')->nullable()->default(NULL)->after('updated_at')->comment('custom job star rating comments column');
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
