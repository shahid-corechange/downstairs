<?php

namespace App\Http\Controllers\CashierProduct;

use App\Contracts\StorageService;
use App\DTOs\Product\CreateProductRequestDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\Enums\Azure\BlobStorage\BlobStorageContainerEnum;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use App\Enums\Product\ProductStatusEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CreateProductArticleJob;
use App\Models\Product;
use App\Models\StoreProduct;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CashierProductController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $storeId = request()->session()->get('store_id');
        $queries = $this->getQueries(
            defaultFilter: [
                'stores_id_eq' => $storeId,
            ],
        );
        $paginatedData = Product::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ProductResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Store a new product
     */
    public function store(CreateProductRequestDTO $request, StorageService $storage): RedirectResponse
    {
        $data = $request->toArray();
        $storeId = request()->session()->get('store_id');

        if (! $request->isOptional('thumbnail')) {
            $filename = generate_filename('product', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['thumbnail_image'] = $url;
        }

        $product = DB::transaction(function () use ($data, $storeId) {
            $product = Product::create([
                ...$data,
                'price' => $data['price'] / (1 + $data['vat_group'] / 100),
            ]);

            // set name & description
            $product->translations()->createMany([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);

            $product->categories()->sync($data['category_ids']);

            // Associate product to store
            StoreProduct::create([
                'store_id' => $storeId,
                'product_id' => $product->id,
                'status' => ProductStatusEnum::Available(),
            ]);

            return $product;
        });

        // save to fortnox
        CreateProductArticleJob::dispatchAfterResponse($product);

        return back()->with('success', __('product created successfully'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $storeId = request()->session()->get('store_id');

        // detach from store
        StoreProduct::where('product_id', $product->id)->where('store_id', $storeId)->delete();

        return back()->with('success', __('product deleted successfully'));
    }
}
