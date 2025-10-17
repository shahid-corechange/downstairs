<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InitialCashierSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StoreSeeder::class,
            StoreProductSeeder::class,
            LaundryOrderSeeder::class,
            LaundryOrderProductSeeder::class,
            LaundryOrderHistorySeeder::class,
            StoreSaleSeeder::class,
        ]);
    }
}
