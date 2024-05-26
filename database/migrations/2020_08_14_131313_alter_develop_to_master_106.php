<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlterDevelopToMaster106 extends Migration
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
            $table->string('last_updated_by')->nullable()->default(null);
            $table->string('assignees')->nullable()->default(null)->after('updated_at')->comment('assignees column');
            $table->string('custom_delivery_time')->nullable()->default(null)->after('updated_at')->comment('custom delivery_time column');
        });
        
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('objectguid')->nullable();
            $table->string('username', 50)->nullable()->unique()->index();
            $table->string('password')->nullable();

            $table->timestamps();
            $table->softDeletes();
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
