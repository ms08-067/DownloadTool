<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirpathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dirpaths', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path_folder');
            $table->unsignedInteger('parent_id')->nullable()->default(null);
            $table->unsignedInteger('lft')->nullable()->default(null);
            $table->unsignedInteger('rght')->nullable()->default(null);
            $table->unsignedInteger('level');
            $table->unsignedTinyInteger('has_pictures');
            $table->unsignedInteger('task_id');
            $table->unsignedTinyInteger('is_example');
            $table->unsignedTinyInteger('is_assigned');
            $table->timestamps();

            $table->index(["lft", "rght", "task_id"], 'lft');

            $table->index(["is_assigned"]);

            $table->index(["lft", "rght", "task_id"], 'lft_2');

            $table->index(["task_id"]);

            $table->index(["parent_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('dirpaths');
     }
}
