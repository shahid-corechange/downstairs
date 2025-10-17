<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StoreSale;
use Illuminate\Database\Seeder;

class StoreSaleSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            StoreSale::factory()->count(10)->create()->each(function (StoreSale $storeSale) {
                $this->addProducts($storeSale, fake()->numberBetween(5, 8));
            });
        } else {
            StoreSale::factory()->count(5)->create()->each(function (StoreSale $storeSale) {
                $this->addProducts($storeSale);
            });
        }
    }

    private function addProducts(StoreSale $storeSale, int $count = 1): void
    {
        $products = Product::inRandomOrder()->limit($count)->get();
        $products = $products->map(function (Product $product) {
            return [
                'product_id' => $product->id,
                'quantity' => fake()->numberBetween(1, 3),
                'note' => fake()->sentence(),
                'name' => $product->name,
                'price' => $product->price,
                'vat_group' => $product->vat_group,
                'discount' => fake()->randomElement([0, 10, 20, 30, 40, 50]),
            ];
        });

        $storeSale->products()->createMany($products);
    }
}
