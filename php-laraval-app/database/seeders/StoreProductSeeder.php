<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use DB;
use Illuminate\Database\Seeder;

class StoreProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // delete products that are moved to the addons
        Product::whereIn('id', [1, 2, 3, 4, 7])->forceDelete();

        $stores = Store::all();
        $storeIds = $stores->pluck('id')->toArray();

        if (app()->environment() !== 'testing') {
            foreach ($this->getProducts() as $product) {
                $nameData = $product['name'];
                $descriptionData = $product['description'];
                $categoryId = $product['category'];
                $isLaundry = $product['is_laundry'];

                DB::transaction(function () use (
                    $product,
                    $storeIds,
                    $nameData,
                    $descriptionData,
                    $categoryId,
                    $isLaundry,
                ) {
                    $product = Product::create($product);

                    $product->translations()->createMany([
                        to_translation('name', $nameData),
                        to_translation('description', $descriptionData),
                    ]);

                    $product->categories()->syncWithoutDetaching($categoryId);
                    $product->stores()->syncWithoutDetaching($storeIds);
                    if ($isLaundry) {
                        $product->addons()->syncWithoutDetaching([
                            config('downstairs.addons.laundry.id'),
                        ]);
                    }
                });
            }
        } else {
            foreach ($this->translations() as $translation) {
                DB::transaction(function () use ($storeIds, $translation) {
                    $product = Product::factory()->create();

                    $product->categories()->sync([config('downstairs.categories.store.id')]);
                    $product->translations()->createMany([
                        to_translation('name', $translation['name']),
                        to_translation('description', $translation['description']),
                    ]);
                    $product->stores()->syncWithoutDetaching($storeIds);
                });
            }
        }
    }

    private function getProducts(): array
    {
        return json_decode(file_get_contents(storage_path('app/seeders/products.json')), true);
    }

    private function translations(): array
    {
        return [
            [
                'name' => [
                    'en_US' => 'Product Sales Misc.',
                    'sv_SE' => 'Produkt Försäljning Div.',
                ],
                'description' => [
                    'en_US' => 'Product Sales Misc.',
                    'sv_SE' => 'Produkt Försäljning Div.',
                ],
            ],
            [
                'name' => [
                    'en_US' => 'Shoe Cream Collonil',
                    'sv_SE' => 'Skokräm Collonil',
                ],
                'description' => [
                    'en_US' => 'Shoe Cream Collonil',
                    'sv_SE' => 'Skokräm Collonil',
                ],
            ],
            [
                'name' => [
                    'en_US' => 'Washologi Fabric Softener',
                    'sv_SE' => 'Washologi Sköljmedel',
                ],
                'description' => [
                    'en_US' => 'Washologi Fabric Softener',
                    'sv_SE' => 'Washologi Sköljmedel',
                ],
            ],
            [
                'name' => [
                    'en_US' => 'Washologi Cotton Blossom Fabric Softener',
                    'sv_SE' => 'Washologi Bomullsblomma Sköljmedel',
                ],
                'description' => [
                    'en_US' => 'Washologi Cotton Blossom Fabric Softener',
                    'sv_SE' => 'Washologi Bomullsblomma Sköljmedel',
                ],
            ],
        ];
    }
}
