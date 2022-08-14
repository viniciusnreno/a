<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RafflesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $raffles = [
            ['dt_start' => '2022-01-11', 'dt_end' => '2022-03-31', 'dt_raffle' => '2022-03-31', 'verifying_digit' => 1]
        ];

        foreach($raffles as $item){
            DB::table('raffles')->insert($item);
        }
    }
}
