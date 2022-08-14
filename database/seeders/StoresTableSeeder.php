<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $stores = [
            ['store_cnpj' => 'None', 'store_name' => 'Loccitane Brésil', 'store_state' => 'BR', 'store_city' => '']
            
        ];

        unset($stores);


        $files = [
            'storage/app/lojas-assai.csv',
            'storage/app/lojas-atacadao.csv',
            'storage/app/lojas-big.csv',
            'storage/app/lojas-carrefour.csv',
            'storage/app/lojas-compre-bem.csv',
            'storage/app/lojas-pao-de-acucar.csv',
            'storage/app/lojas-big-super.csv',
        ];
        
        foreach($files as $file){
            $stores = array_map('str_getcsv', file($file));


            foreach($stores as $item){
                $cnpj = str_pad(preg_replace('/[^\d]/', '', $item[3]), 14, "0", STR_PAD_LEFT);

                if(strlen($cnpj) == 15){
                    $cnpj = substr($cnpj, -14);
                }
                
                if($item[3] != '#N/A'){
                    $findCNPJ = DB::table('stores')->where('store_cnpj', $cnpj)->get()->count();

                    if($findCNPJ == 0){
                        DB::table('stores')->insert(['store_name' => ucwords(strtolower(trim($item[0]))), 'store_city' => ucwords(strtolower(trim($item[1]))), 'store_state' => strtoupper(trim($item[2])), 'store_cnpj' => $cnpj]);
                        // DB::table('stores')->insert($item);
                    }
                }
            }

            unset($stores);
        }
    }
}
