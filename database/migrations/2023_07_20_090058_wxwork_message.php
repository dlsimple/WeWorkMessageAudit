<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WxworkMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wxwork_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seq');
            $table->string('action', 10);
            $table->string('msgid', 64);
            $table->string('from', 64)->nullable();
            $table->string('roomid', 64);
            $table->json('tolist');
            $table->unsignedBigInteger('msgtime');
            $table->string('msgtype', 16);
            $table->text('msgcontent')->nullable();
            $table->json('encrypt_content');
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
        Schema::dropIfExists('wxwork_messages');
    }
}
