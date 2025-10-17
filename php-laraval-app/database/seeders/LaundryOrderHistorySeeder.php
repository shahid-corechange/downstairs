<?php

namespace Database\Seeders;

use App\Models\LaundryOrder;
use App\Models\LaundryOrderHistory;
use Illuminate\Database\Seeder;

class LaundryOrderHistorySeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            $laundryOrders = LaundryOrder::all();
            foreach ($laundryOrders as $laundryOrder) {
                LaundryOrderHistory::factory()->count(5)->forOrder($laundryOrder->id)->create();
            }
        } else {
            LaundryOrderHistory::factory()->count(5)->create();
        }
    }
}
