<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWhatsappChatProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_chat_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('chat_id')->unsigned();
            $table->bigInteger('product_id')->nullable()->unsigned();
            $table->integer('qty')->nullable();
            $table->boolean('completed')->default(0);
        
            $table->foreign('chat_id')->references('id')->on('whatsapp_chat')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_chat_products');
    }
}
