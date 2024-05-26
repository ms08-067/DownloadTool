<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster117 extends Migration
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
            $table->string('custom_output_expected')->nullable()->default(null)->after('updated_at')->comment('custom hashtag column');
            $table->tinyInteger('preview_req')->default(2)->after('updated_at')->comment('1: required to provide preview for customer 2: not required to provide preview for customer');
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
