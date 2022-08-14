<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('whatsappuser_id')->nullable()->unique()->unsigned();
            $table->string('mobile', 20);
            $table->string('name', 120)->nullable();
            $table->enum('user_type', ['F', 'J'] )->default('F');
            $table->string('cpf', 30)->nullable()->unique();
            $table->string('cnpj', 30)->nullable()->unique();
            $table->string('company_person_name', 120)->nullable();
            $table->string('company_person_cpf', 30)->nullable()->unique();
            $table->date('birth_date')->nullable();
            $table->string('email', 120)->unique();
            $table->string('address', 250)->nullable();
            $table->string('address_number', 15)->nullable();
            $table->string('address_note', 70)->nullable();
            $table->string('neighborhood', 70)->nullable();
            $table->string('zipcode', 10)->nullable();
            $table->string('city', 60)->nullable();
            $table->char('state', 2)->nullable();
            $table->enum('gender', ['F', 'M', 'O'])->nullable();
            $table->string('ip', 45)->nullable();   
            $table->boolean('active')->default(0);
            $table->boolean('forbidden')->default(0);
            $table->bigInteger('ficou_sabendo_id')->nullable()->unsigned();
            $table->boolean('receive_information_email')->default(0);
            $table->boolean('receive_information_whatsapp')->default(0);
            $table->boolean('agree_regulation')->default(0);
            $table->boolean('keep_data')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 50)->default('user');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('whatsappuser_id')->references('id')->on('whatsapp_users');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
