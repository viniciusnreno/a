<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWhatsappLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('to', 30)->nullable();
            $table->string('id_message', 200)->nullable();
            $table->text('body')->nullable();
            $table->string('type', 100)->nullable();
            $table->string('sender_name', 100)->nullable();
            $table->boolean('from_me')->nullable();
            $table->string('author', 200)->nullable();
            $table->string('chat_id', 200)->nullable();
            $table->integer('messageNumber')->nullable();
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
        Schema::dropIfExists('whatsapp_log');
    }
}
