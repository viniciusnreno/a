<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlockedUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds
     *
     * @return void
     */
    public function run()
    {
        $codes = [];

        foreach($codes as $item){
            DB::table('blocked_users')->insert([
                'document' => $item
            ]);
        }
    }
}
