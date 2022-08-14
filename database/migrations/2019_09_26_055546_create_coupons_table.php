<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('raffle_id')->unsigned();
            $table->bigInteger('code_id')->nullable()->unsigned();
            $table->bigInteger('whatsappchat_id')->nullable()->unsigned();
            $table->bigInteger('store_id')->unsigned()->nullable();
            $table->bigInteger('prize_id')->nullable()->unsigned();
            $table->string('invoice', 250);
            $table->string('company_cnpj', 45);
            $table->string('company_name', 45)->nullable();
            $table->string('coupon_number', 45);
            $table->decimal('amount', 10, 2);
            $table->date('buy_date');
            $table->char('state', 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('instant_prize_reason')->nullable();
            $table->text('instant_prize_hash')->nullable();
            $table->string('friend_email', 255)->nullable();
            $table->string('friend_name', 255)->nullable();
            $table->string('friend_social', 255)->nullable();
            $table->boolean('friend_status')->default(0);
            $table->boolean('friend_payback')->default(0);
            $table->string('required_product', 10)->nullable();
            $table->decimal('prize_value', 10, 2)->nullable();
            $table->boolean('cna_mini_curso')->default(0);
            $table->text('picpay_error')->nullable();
            $table->text('picpay_return')->nullable();
            $table->string('ip', 50)->nullable();
            $table->boolean('status')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('raffle_id')->references('id')->on('raffles')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('code_id')->references('id')->on('codes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('whatsappchat_id')->references('id')->on('whatsapp_chat')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('coupons');
    }
}
