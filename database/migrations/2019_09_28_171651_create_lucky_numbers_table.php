<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuckyNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lucky_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('coupon_id')->unsigned()->nullable();
            $table->bigInteger('raffle_id')->unsigned();
            $table->string('number', 20);
            $table->string('ip', 45);
            $table->boolean('final')->default(0);
            $table->bigInteger('parent_coupon_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('parent_coupon_id')->references('id')->on('coupons')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('raffle_id')->references('id')->on('raffles')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lucky_numbers');
    }
}
