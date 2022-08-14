<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrizesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $prizes = [
            ['prize' => 'R$ 30,00', 'qty' => 100, 'label' => 'trinta', 'text_value' => '30', 'instant' => 1, 'status' => 1],
            ['prize' => 'R$ 50,00', 'qty' => 50, 'label' => 'cinquenta', 'text_value' => '50', 'instant' => 1, 'status' => 0],
            ['prize' => 'R$ 100,00', 'qty' => 20, 'label' => 'cem', 'text_value' => '100', 'instant' => 1, 'status' => 0],
            ['prize' => 'R$ 200,00', 'qty' => 6, 'label' => 'duzentos', 'text_value' => '200', 'instant' => 1, 'status' => 0],
            ['prize' => 'R$ 300,00', 'qty' => 2, 'label' => 'trezentos', 'text_value' => '300', 'instant' => 1, 'status' => 0],
            ['prize' => 'R$ 400,00', 'qty' => 1, 'label' => 'quatrocentos', 'text_value' => '400', 'instant' => 1, 'status' => 0],
            ['prize' => 'R$ 500,00', 'qty' => 1, 'label' => 'quinhentos', 'text_value' => '500', 'instant' => 1, 'status' => 0]
        ];

        foreach($prizes as $item){

            DB::table('prizes')->insert(['prize' => trim($item['prize']), 'qty' => $item['qty'], 'label' => $item['label'], 'text_value' => $item['text_value'], 'instant' => (int) $item['instant'], 'status' => (int) $item['status']]);
        }
    }
}
