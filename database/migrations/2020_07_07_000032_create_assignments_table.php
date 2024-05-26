<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id')->nullable()->default(null);
            $table->string('case_id', 20);
            $table->unsignedInteger('dirpath_id')->nullable()->default(null);
            $table->string('file_name');
            $table->unsignedTinyInteger('actived')->default('1');
            $table->text('image_thumb_source')->nullable()->default(null);
            $table->string('note')->nullable()->default(null);
            $table->unsignedTinyInteger('is_assigned')->default('0');
            $table->unsignedInteger('user_id')->nullable()->default(null)->comment('user by extern redo');
            $table->string('group')->nullable()->default(null)->comment('dirpath_id/key_group');
            $table->timestamps();

            $table->index(["file_name"]);

            $table->index(["dirpath_id"]);

            $table->index(["case_id"]);

            $table->index(["task_id"]);


            $table->foreign('task_id', 'task_id')
                ->references('id')->on('tasks')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('dirpath_id', 'dirpath_id')
                ->references('id')->on('dirpaths')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('task_id', 'task_id')
                ->references('id')->on('tasks')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('assignments');
     }
}
