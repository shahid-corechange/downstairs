<?php

namespace Database\Seeders;

use App\Models\LaundryOrder;
use App\Models\Product;
use Illuminate\Database\Seeder;

class LaundryOrderProductSeeder extends Seeder
{
    public function run(): void
    {
        $limit = app()->environment() !== 'testing' ? 3 : 1;
        $laundryOrders = LaundryOrder::all();

        foreach ($laundryOrders as $laundryOrder) {
            $products = Product::inRandomOrder()->limit($limit)->get();
            $productsData = $products->map(function (Product $product) {
                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => fake()->numberBetween(1, 5),
                    'note' => fake()->sentence(),
                    'price' => $product->price,
                    'vat_group' => $product->vat_group,
                    'discount' => fake()->randomElement([0, 10, 20, 30, 40, 50]),
                    'has_rut' => $product->has_rut,
                ];
            });

            $laundryOrder->products()->createMany($productsData);
        }
    }
}
