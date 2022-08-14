<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(ProductsTableSeeder::class);
        $this->call(FicouSabendoTableSeeder::class);
        $this->call(RafflesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(PrizesTableSeeder::class);
        $this->call(CodesTableSeeder::class);
        $this->call(BlockedUsersTableSeeder::class);
        // $this->call(StoresTableSeeder::class);
    }
}
