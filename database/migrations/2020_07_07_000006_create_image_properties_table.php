<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_properties', function (Blueprint $table) {
            $table->increments('document_id');
            $table->string('path')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->tinyInteger('has_transparent')->nullable()->default(null);
            $table->text('layers')->nullable()->default(null);
            $table->text('mask_layers')->nullable()->default(null);
            $table->integer('user_id')->nullable()->default(null);
            $table->string('username', 50)->nullable()->default(null);
            $table->text('logs')->nullable()->default(null);
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
       Schema::dropIfExists('image_properties');
     }
}
