<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWhatsappUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mobile', 20)->unique();
            $table->string('full_mobile', 20)->unique()->nullable();
            $table->string('name', 250)->nullable();
            $table->string('email', 120)->nullable()->unique();
            $table->enum('user_type', ['F', 'J'])->nullable()->default('F');
            $table->string('cpf', 30)->nullable()->unique();
            $table->string('cnpj', 30)->nullable()->unique();
            $table->string('company_person_name', 250)->nullable();
            $table->string('company_person_cpf', 30)->nullable();
            $table->char('state', 2)->nullable();
            $table->string('city', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->boolean('agree_regulation')->default(0);
            $table->string('password')->nullable();
            $table->boolean('completed')->default(0);
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
        Schema::dropIfExists('whatsapp_users');
    }
}
