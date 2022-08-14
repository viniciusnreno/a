<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            'Bolinhos Panquinho' => 'Bolinho',
            'Bolinhos Bebezinho' => 'Bolinho',
            'Pães de Mel' => 'Pão de Mel',
            'Biscoitos Recheados' => 'Biscoito',
            'Biscoitos Doces' => 'Biscoito',
            'Biscoitos Salgados' => 'Biscoito',
            'Wafers' => 'Wafer',
            'Rosquinhas' => 'Rosquinha'
        ];

        foreach($products as $item => $value){
            $howMany = 1;
            DB::table('products')->insert([
                'product' => $item,
                'category' => $value,
                'many' => $howMany,
                'selected' => $value == 'Recheado' ? 1 : 0,
                'status' => 1
            ]);
        }   
    }
}
