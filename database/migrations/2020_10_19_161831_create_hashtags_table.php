<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHashtagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25);
            $table->string('last_updated_by')->nullable()->default(null);

            $table->unique(["name"]);
            $table->timestamps();
        });

        $data = [
            [
                'name' => 'ASIA',
            ],
            [
                'name' => 'GERMANY',
            ],
            [
                'name' => 'FAVOURITE',
            ],
        ];

        DB::table('hashtags')->delete();
        DB::table('hashtags')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hashtags');
    }
}
