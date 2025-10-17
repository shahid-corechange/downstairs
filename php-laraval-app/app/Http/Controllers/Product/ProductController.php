<?php

namespace App\Http\Controllers\Product;

use App\Contracts\StorageService;
use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\Category\CategoryResponseDTO;
use App\DTOs\Product\CreateProductRequestDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Product\UpdateProductRequestDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\Enums\Azure\BlobStorage\BlobStorageContainerEnum;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CreateProductArticleJob;
use App\Jobs\UpdateProductArticleJob;
use App\Models\Addon;
use App\Models\Category;
use App\Models\LaundryOrder;
use App\Models\PriceAdjustmentRow;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\Service;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\Subscription;
use App\Services\PriceAdjustmentService;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'categories',
        'addons',
        'services',
        'stores',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'name',
        'description',
        'unit',
        'priceWithVat',
        'creditPrice',
        'vatGroup',
        'hasRut',
        'status',
        'thumbnailImage',
        'color',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'categories.id',
        'categories.name',
        'addons.id',
        'addons.name',
        'services.id',
        'services.name',
        'stores.id',
        'stores.name',
        'translations',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            size: -1,
            show: 'all',
        );
        $paginatedData = Product::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Product/Overview/index', [
            'products' => ProductResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'categories' => $this->getCategories(),
            'addons' => $this->getAddons(),
            'services' => $this->getServices(),
            'stores' => $this->getStores(),
        ]);
    }

    private function getCategories()
    {
        $onlys = [
            'id',
            'name',
        ];
        $categories = Category::selectWithRelations($onlys)->get();

        return CategoryResponseDTO::collection($categories)->only(...$onlys);
    }

    private function getAddons()
    {
        $onlys = [
            'id',
            'name',
        ];
        $addons = Addon::selectWithRelations($onlys)->get();

        return AddonResponseDTO::collection($addons)->only(...$onlys);
    }

    private function getServices()
    {
        $onlys = [
            'id',
            'name',
            'type',
            'membershipType',
            'hasRut',
        ];
        $services = Service::selectWithRelations($onlys)->get();

        return ServiceResponseDTO::collection($services)->only(...$onlys);
    }

    private function getStores()
    {
        $onlys = [
            'id',
            'name',
        ];
        $stores = Store::selectWithRelations($onlys)->get();

        return StoreResponseDTO::collection($stores)->only(...$onlys);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Product::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ProductResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProductRequestDTO $request, StorageService $storage): RedirectResponse
    {
        $data = $request->toArray();

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

        $product = DB::transaction(function () use ($data) {
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

            if (isset($data['service_ids'])) {
                $product->services()->sync($data['service_ids']);
            }

            if (isset($data['addon_ids'])) {
                $product->addons()->sync($data['addon_ids']);
            }

            if (isset($data['store_ids'])) {
                $product->stores()->sync($data['store_ids']);
            }

            return $product;
        });

        // save to fortnox
        CreateProductArticleJob::dispatchAfterResponse($product);

        return back()->with('success', __('product created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateProductRequestDTO $request,
        Product $product,
        StorageService $storage
    ): RedirectResponse {

        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            if ($product->thumbnail_image) {
                $oldFilename = basename($product->thumbnail_image);
                $storage->delete(BlobStorageContainerEnum::Images(), $oldFilename);
            }

            $filename = generate_filename('product', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['thumbnail_image'] = $url;
        }

        // update price and vat
        $vat = $request->isNotOptional('vat_group') ? $request->vat_group : $product->vat_group;
        $price = $request->isNotOptional('price') ?
            $request->price / (1 + $vat / 100) : $product->price;
        $priceWithVat = $request->isNotOptional('price') ? $request->price : $product->price_with_vat;

        DB::transaction(function () use (
            &$product,
            $data,
            $vat,
            $price,
            $priceWithVat,
        ) {
            // Update product price adjustment if price or vat changed
            if ($price !== $product->price || $vat !== $product->vat_group) {
                PriceAdjustmentService::updatePriceAdjustmentRow($product, $priceWithVat, $vat);
            }

            // Update schedule cleaning item
            if ($price !== $product->price) {
                ScheduleItem::whereItemable($product)
                    ->whereHas('schedule', function (Builder $query) {
                        $query->future();
                    })
                    ->update(['price' => $price]);
            }

            // Update product
            $product->update([
                ...$data,
                'price' => $price,
                'vat_group' => $vat,
            ]);

            // set name & description
            $product->updateTranslations([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);

            $product->categories()->sync($data['category_ids']);
            if (isset($data['service_ids'])) {
                $product->services()->sync($data['service_ids']);
            }

            if (isset($data['addon_ids'])) {
                $product->addons()->sync($data['addon_ids']);
            }

            if (isset($data['store_ids'])) {
                $product->stores()->sync($data['store_ids']);
            }
        });

        UpdateProductArticleJob::dispatchAfterResponse($product);

        return back()->with('success', __('product updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // cannot delete mandatory products
        if (in_array($product->id, config('downstairs.products.systemIds'))) {
            return back()->with(
                'error',
                __('cannot delete this product, it is used in the system')
            );
        }

        $useInSchedules = Schedule::Booked()
            ->whereHas('items', function ($query) use ($product) {
                $query->whereItemable($product);
            })
            ->exists();
        if ($useInSchedules) {
            return back()->with('error', __('product still use in active schedules'));
        }

        $useInSubscriptions = Subscription::whereHas(
            'items',
            function ($query) use ($product) {
                $query->whereItemable($product);
            }
        )
            ->exists();

        if ($useInSubscriptions) {
            return back()->with('error', __('product still use in subscriptions'));
        }

        $useInLaundryOrders = LaundryOrder::whereHas(
            'products',
            function ($query) use ($product) {
                $query->where('product_id', $product->id);
            }
        )
            ->exists();

        if ($useInLaundryOrders) {
            return back()->with('error', __('product still use in laundry orders'));
        }

        DB::transaction(function () use ($product) {
            // Delete price adjustment rows
            PriceAdjustmentRow::product($product->id)->pending()->delete();

            // Delete product in store
            StoreProduct::where('product_id', $product->id)->delete();

            $product->delete();
        });

        return back()->with('success', __('product deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Product $product): RedirectResponse
    {
        $product->restore();

        return back()->with('success', __('product restored successfully'));
    }
}
