<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWhatsappStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('zenvia_id');
            $table->string('zenvia_timestamp', 60);
            $table->enum('zenvia_type',['MESSAGE', 'MESSAGE_STATUS'])->nullable();
            $table->string('zenvia_subscriptionId', 60)->nullable();
            $table->string('zenvia_channel', 60)->nullable();
            $table->string('zenvia_messageId', 250)->nullable();
            $table->bigInteger('zenvia_contentIndex')->nullable();
            $table->string('zenvia_status_timestamp', 60)->nullable();
            $table->enum('zenvia_status_code', ["REJECTED", "SENT", "DELIVERED", "NOT_DELIVERED", "READ"])->nullable();
            $table->string('zenvia_status_description', 240)->nullable();
            $table->string('zenvia_status_cause_channelErrorCode', 10)->nullable();
            $table->text('zenvia_status_cause_reason')->nullable();
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
        Schema::dropIfExists('whatsapp_status');
    }
}
