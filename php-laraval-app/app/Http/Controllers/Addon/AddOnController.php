<?php

namespace App\Http\Controllers\Addon;

use App\Contracts\StorageService;
use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\Addon\CreateAddonRequestDTO;
use App\DTOs\Addon\UpdateAddonRequestDTO;
use App\DTOs\Category\CategoryResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\Enums\Azure\BlobStorage\BlobStorageContainerEnum;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CreateAddonArticleJob;
use App\Jobs\UpdateAddonArticleJob;
use App\Models\Addon;
use App\Models\Category;
use App\Models\PriceAdjustmentRow;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\PriceAdjustmentService;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AddOnController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'services',
        'categories',
        'products',
        'tasks',
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
        'services.id',
        'services.name',
        'services.type',
        'categories.id',
        'categories.name',
        'products.id',
        'products.name',
        'tasks.id',
        'tasks.name',
        'tasks.description',
        'tasks.translations',
        'translations',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            size: -1,
            show: 'all'
        );
        $paginatedData = Addon::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('AddOn/Overview/index', [
            'addons' => AddonResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'services' => $this->getServices(),
            'categories' => $this->getCategories(),
            'products' => $this->getProducts(),
        ]);
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

    private function getCategories()
    {
        $onlys = [
            'id',
            'name',
        ];
        $categories = Category::selectWithRelations($onlys)->get();

        return CategoryResponseDTO::collection($categories)->only(...$onlys);
    }

    private function getProducts()
    {
        $onlys = [
            'id',
            'name',
        ];
        $products = Product::selectWithRelations($onlys)->get();

        return ProductResponseDTO::collection($products)->only(...$onlys);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Addon::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            AddonResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Store a newly created resource in storage.
     * Also save meta data
     */
    public function store(CreateAddonRequestDTO $request, StorageService $storage): RedirectResponse
    {
        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            $filename = generate_filename('addon', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['thumbnail_image'] = $url;
        }

        $addon = DB::transaction(function () use ($data) {
            $addon = Addon::create([
                ...$data,
                'price' => $data['price'] / (1 + $data['vat_group'] / 100),
            ]);

            // set name & description
            $addon->translations()->createMany([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);

            $addon->categories()->sync($data['category_ids']);
            $addon->services()->sync($data['service_ids']);

            if (isset($data['product_ids'])) {
                $addon->products()->sync($data['product_ids']);
            }

            // set tasks
            foreach ($data['tasks'] as $task) {
                /** @var \App\Models\CustomTask $customTask */
                $customTask = $addon->tasks()->create([]);
                $customTask->translations()->createMany([
                    to_translation('name', $task['name']),
                    to_translation('description', $task['description']),
                ]);
            }

            return $addon;
        });

        // save to fortnox
        CreateAddonArticleJob::dispatchAfterResponse($addon);

        return back()->with('success', __('add on created successfully'));
    }

    /**
     * Update the specified resource in storage.
     * Also save meta data
     */
    public function update(
        UpdateAddonRequestDTO $request,
        Addon $addon,
        StorageService $storage
    ): RedirectResponse {

        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            if ($addon->thumbnail_image) {
                $oldFilename = basename($addon->thumbnail_image);
                $storage->delete(BlobStorageContainerEnum::Images(), $oldFilename);
            }

            $filename = generate_filename('service', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['thumbnail_image'] = $url;
        }

        // update price and vat
        $vat = $request->isNotOptional('vat_group') ? $request->vat_group : $addon->vat_group;
        $price = $request->isNotOptional('price') ?
            $request->price / (1 + $vat / 100) : $addon->price;
        $priceWithVat = $request->isNotOptional('price') ? $request->price : $addon->price_with_vat;

        DB::transaction(function () use (
            &$addon,
            $data,
            $vat,
            $price,
            $priceWithVat,
        ) {
            // Update product price adjustment if price or vat changed
            if ($price !== $addon->price || $vat !== $addon->vat_group) {
                PriceAdjustmentService::updatePriceAdjustmentRow($addon, $priceWithVat, $vat);
            }

            if ($price !== $addon->price) {
                ScheduleItem::whereItemable($addon)
                    ->whereHas('schedule', function (Builder $query) {
                        $query->future();
                    })
                    ->update(['price' => $price]);
            }

            // Update product
            $addon->update([
                ...$data,
                'price' => $price,
                'vat_group' => $vat,
            ]);

            // set name & description
            $addon->updateTranslations([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);

            $addon->categories()->sync($data['category_ids']);
            $addon->services()->sync($data['service_ids']);

            if (isset($data['product_ids'])) {
                $addon->products()->sync($data['product_ids']);
            }
        });

        UpdateAddonArticleJob::dispatchAfterResponse($addon);

        return back()->with('success', __('add on updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Addon $addon): RedirectResponse
    {
        $useInSchedules = Schedule::booked()
            ->whereHas('items', function ($query) use ($addon) {
                $query->whereItemable($addon);
            })
            ->exists();
        if ($useInSchedules) {
            return back()->with('error', __('add on still use in active schedules'));
        }

        $useInSubscriptions = Subscription::whereHas(
            'items',
            function ($query) use ($addon) {
                $query->whereItemable($addon);
            }
        )
            ->exists();

        if ($useInSubscriptions) {
            return back()->with('error', __('add on still use in subscriptions'));
        }

        DB::transaction(function () use ($addon) {
            // Delete price adjustment rows
            PriceAdjustmentRow::product($addon->id)->pending()->delete();

            $addon->delete();
        });

        return back()->with('success', __('add on deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Addon $addon): RedirectResponse
    {
        $addon->restore();

        return back()->with('success', __('add on restored successfully'));
    }
}
