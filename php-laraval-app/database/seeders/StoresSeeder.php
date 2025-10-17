<?php

namespace Database\Seeders;

use App\Enums\Store\StoreProductStatusEnum;
use App\Models\Address;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProduct;
use Illuminate\Database\Seeder;

class StoresSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->getStores() as $storeData) {
            $address = Address::factory()->create();

            $store = Store::create([
                'name' => $storeData['name'],
                'company_number' => $storeData['company_number'],
                'phone' => $storeData['phone'],
                'dial_code' => $storeData['dial_code'],
                'email' => $storeData['email'],
                'address_id' => $address->id,
            ]);

            $products = Product::inRandomOrder()->limit(3)->get();
            foreach ($products as $product) {
                StoreProduct::insert([
                    'store_id' => $store->id,
                    'product_id' => $product->id,
                    'status' => StoreProductStatusEnum::Active(),
                ]);
            }
        }
    }

    private function getStores(): array
    {
        $faker = fake();
        $phone = $faker->randomNumber(3, true);

        return [
            [
                'name' => $faker->company,
                'company_number' => $faker->randomNumber(8, true),
                'phone' => "46726416{$phone}",
                'dial_code' => '46',
                'email' => $faker->email,
            ],
            [
                'name' => $faker->company,
                'company_number' => $faker->randomNumber(8, true),
                'phone' => "46726416{$phone}",
                'dial_code' => '46',
                'email' => $faker->email,
            ],
        ];
    }
}
