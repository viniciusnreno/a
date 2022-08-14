<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FicouSabendoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'Amigos',
            'Comunicação em Lojas e Supermercados',
            'Site da Broto Legal ou sites de busca (google, etc)',
            'Influenciadores'
        ];

        foreach($types as $item){
            DB::table('ficou_sabendo')->insert([
                'descricao' => $item
            ]);
        }
    }
}
