<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster114 extends Migration
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
            $table->string('custom_hashtag')->nullable()->default(null)->after('updated_at')->comment('custom hashtag column');
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
