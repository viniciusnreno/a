<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWhatsappChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_chat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('whatsappuser_id')->unsigned();
            $table->bigInteger('raffle_id')->unsigned();
            $table->bigInteger('prize_id')->unsigned()->nullable();
            $table->string('name', 100)->nullable();
            $table->string('mobile', 45)->nullable();
            $table->string('mobile_full', 45)->nullable();
            $table->string('invoice', 250)->nullable();
            $table->string('invoice_local', 250)->nullable();
            $table->string('company_cnpj', 45)->nullable();
            $table->string('coupon_number', 45)->nullable();
            $table->string('required_product', 10)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('cpf', 45)->nullable();
            $table->dateTime('buy_date')->nullable();
            $table->char('buy_state', 2)->nullable();
            $table->boolean('status')->default(0)->nullable();
            $table->boolean('imported')->default(0)->nullable();
            $table->boolean('products')->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('whatsappuser_id')->references('id')->on('whatsapp_users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('raffle_id')->references('id')->on('raffles')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('prize_id')->references('id')->on('prizes')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_chat');
    }
}
