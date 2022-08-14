<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfluencersInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('influencers_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('influencer_id')->unsigned();
            $table->string('profile', 30)->unique();
            $table->string('type', 30);
            $table->string('url', 255)->unique();

            $table->foreign('influencer_id')->references('id')->on('influencers')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('influencers_info');
    }
}
