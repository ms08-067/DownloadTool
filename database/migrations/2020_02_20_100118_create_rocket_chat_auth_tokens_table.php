<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRocketChatAuthTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rocket_chat_auth_tokens', function (Blueprint $table) {
        	$table->increments('id');
        	$table->string('rc_username')->unique()->comment('rocketchat username');
            $table->string('x_auth_token')->comment('rocketchat authentication token');
            $table->string('x_user_id')->comment('rocketchat user id');
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
        Schema::dropIfExists('rocket_chat_auth_tokens');
    }
}
