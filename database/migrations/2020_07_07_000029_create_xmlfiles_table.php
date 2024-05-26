<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmlfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xmlfiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable()->default(null);
            $table->integer('german_id')->nullable()->default(null);
            $table->string('customerFtp')->nullable()->default(null);
            $table->string('jobId', 50);
            $table->string('jobIdTitle')->nullable()->default(null);
            $table->string('jobTitle')->nullable()->default(null);
            $table->unsignedSmallInteger('amount')->default('0');
            $table->dateTime('deliveryProduction')->nullable()->default(null);
            $table->unsignedTinyInteger('isExpress')->default('0');
            $table->unsignedSmallInteger('minutesPerImage')->default('0');
            $table->text('jobInfo')->nullable()->default(null);
            $table->text('jobInfoProduction')->nullable()->default(null);
            $table->text('services')->nullable()->default(null);
            $table->unsignedTinyInteger('is_created')->default('0');
            $table->tinyInteger('is_empty')->default('0');
            $table->tinyInteger('try')->default('0');
            $table->string('classify', 20)->nullable()->default(null);
            $table->string('jobType')->nullable()->default(null);
            $table->string('parentJobId', 50)->nullable()->default(null);
            $table->string('originalJobId', 50)->nullable()->default(null);
            $table->string('status', 20)->nullable()->default(null);
            $table->string('tool', 50)->nullable()->default(null);
            $table->timestamps();

            $table->unique(["jobId"], 'jobId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('xmlfiles');
     }
}
