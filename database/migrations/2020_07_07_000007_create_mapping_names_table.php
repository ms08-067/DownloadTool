<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMappingNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mapping_names', function (Blueprint $table) {
            $table->increments('id');
            $table->string('original')->nullable()->default(null);
            $table->string('replacement')->nullable()->default(null);
            $table->string('type', 20)->nullable()->default(null)->comment('file, folder');
            $table->string('case_id', 20)->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('mapping_names');
     }
}
