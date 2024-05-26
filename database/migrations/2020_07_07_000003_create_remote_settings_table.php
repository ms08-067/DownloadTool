<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

    class CreateRemoteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remote_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 50)->nullable()->default(null);
            $table->text('value')->nullable()->default(null);
            $table->string('note')->nullable()->default(null);
        });

        if(env('APP_ENV') == 'dev' || env('APP_ENV') == 'test' || env('APP_ENV') == 'prod'){
            $data = [
                [
                    'id' => 1,
                    'code' => 'queue_thumbnail_amount',
                    'value' => '10',
                    'note' => 'files/time',
                ],
                [
                    'id' => 2,
                    'code' => 'queue_thumbnail_size',
                    'value' => '1024',
                    'note' => 'MB/time',
                ],
                [
                    'id' => 3,
                    'code' => 'queue_thumbnail_photoshop',
                    'value' => 'psd,tif,psb,tiff',
                    'note' => 'photoshop extensions',
                ],
                [
                    'id' => 4,
                    'code' => 'window_special_characters',
                    'value' => '\\ / : * ? \" < > |',
                    'note' => 'separeate by one space, replace by +++++',
                ],
                [
                    'id' => 5,
                    'code' => 'window_max_path_length',
                    'value' => '200',
                    'note' => NULL,
                ]
            ];
            DB::table('remote_settings')->insert($data);
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('remote_settings');
     }
}
