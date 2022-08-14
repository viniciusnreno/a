<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'mobile' => '11996393535',
            'name' => 'Denis Pose',
            'cpf' => '33167005874',
            'birth_date' => '1984-06-07',
            'email' => 'denispose@msn.com',
            // 'address' => 'Rua Doutor Luiz Migliano',
            // 'address_number' => '190',
            // 'address_note' => 'AP 136 BL MISTERIO',
            // 'neighborhood' => 'Jd. vazani',
            // 'zipcode' => '05711000',
            'city' => 'São Paulo',
            'state' => 'SP',
            // 'ficou_sabendo_id' => 1,
            'receive_information_email' => 1,
            'receive_information_whatsapp' => 1,
            // 'keep_invoice' => 1,
            'agree_regulation' => 1,
            'ip' => \Request::ip(),
            'password' => Hash::make('12345'),
            'role' => 'admin'
        ]);
    }
}
