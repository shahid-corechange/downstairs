<?php

namespace App\Console\Commands;

use App\Jobs\CreateProductArticleJob;
use App\Models\Product;
use App\Models\Store;
use App\Models\Translation;
use DB;
use Illuminate\Console\Command;

class AddProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add products from old system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // delete products that are moved to the addons
        Product::whereIn('id', [1, 2, 3, 4, 7])->forceDelete();

        $stores = Store::all();
        $storeIds = $stores->pluck('id')->toArray();

        foreach ($this->getProducts() as $productData) {
            $nameData = $productData['name'];
            $descriptionData = $productData['description'];
            $categoryId = $productData['category'];
            $isLaundry = $productData['is_laundry'];

            // check if product already exists
            $isExists = Translation::where('translationable_type', Product::class)
                ->where('key', 'name')
                ->where('en_US', $nameData['en_US'])->exists();

            if ($isExists) {
                continue;
            }

            $newProduct = DB::transaction(function () use (
                $productData,
                $storeIds,
                $nameData,
                $descriptionData,
                $categoryId,
                $isLaundry,
            ) {
                $product = Product::create($productData);

                $product->translations()->createMany([
                    to_translation('name', $nameData),
                    to_translation('description', $descriptionData),
                ]);

                $product->categories()->sync($categoryId);
                $product->stores()->syncWithoutDetaching($storeIds);
                if ($isLaundry) {
                    $product->addons()->syncWithoutDetaching([
                        config('downstairs.addons.laundry.id'),
                    ]);
                }

                return $product;
            });

            // save to fortnox
            CreateProductArticleJob::dispatchSync($newProduct);
        }
    }

    private function getProducts(): array
    {
        return json_decode(file_get_contents(storage_path('app/seeders/products.json')), true);
    }
}
