<?php

namespace App\Http\Controllers\Service;

use App\Contracts\StorageService;
use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\Category\CategoryResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Service\CreateServiceRequestDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\Service\UpdateServiceRequestDTO;
use App\Enums\Azure\BlobStorage\BlobStorageContainerEnum;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CreateServiceArticleJob;
use App\Jobs\UpdateServiceArticleJob;
use App\Models\Addon;
use App\Models\Category;
use App\Models\PriceAdjustmentRow;
use App\Models\Product;
use App\Models\Service;
use App\Services\PriceAdjustmentService;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'tasks',
        'categories',
        'addons',
        'products',
    ];

    /**
     * Exclude these fields in the response.
     */
    private array $excludes = [
        'price',
        'createdAt',
        'updatedAt',
        'description',
        'fortnoxArticleId',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'type',
        'membershipType',
        'name',
        'vatGroup',
        'priceWithVat',
        'hasRut',
        'thumbnailImage',
        'deletedAt',
        'tasks.id',
        'tasks.name',
        'tasks.description',
        'tasks.translations',
        'categories.id',
        'categories.name',
        'addons.id',
        'addons.name',
        'products.id',
        'products.name',
        'meta',
        'translations',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(size: -1, show: 'all');
        $paginatedData = Service::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Service/Overview/index', [
            'services' => ServiceResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'addons' => $this->getAddons(),
            'categories' => $this->getCategories(),
            'products' => $this->getProducts(),
        ]);
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
        $paginatedData = Service::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ServiceResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateServiceRequestDTO $request, StorageService $storage): RedirectResponse
    {
        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            $filename = generate_filename('service', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['thumbnail_image'] = $url;
        }

        $service = DB::transaction(function () use ($data) {
            $service = Service::create([
                ...$data,
                'price' => $data['price'] / (1 + $data['vat_group'] / 100),
            ]);

            // set name & description
            $service->translations()->createMany([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);

            $service->categories()->sync($data['category_ids']);

            if (isset($data['addon_ids'])) {
                $service->addons()->sync($data['addon_ids']);
            }

            if (isset($data['product_ids'])) {
                $service->products()->sync($data['product_ids']);
            }

            // set tasks
            foreach ($data['tasks'] as $task) {
                /** @var \App\Models\CustomTask $customTask */
                $customTask = $service->tasks()->create([]);
                $customTask->translations()->createMany([
                    to_translation('name', $task['name']),
                    to_translation('description', $task['description']),
                ]);
            }

            return $service;
        });

        CreateServiceArticleJob::dispatchAfterResponse($service);

        return back()->with('success', __('service created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateServiceRequestDTO $request,
        Service $service,
        StorageService $storage
    ): RedirectResponse {
        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            if ($service->thumbnail_image) {
                $oldFilename = basename($service->thumbnail_image);
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
        $vat = $request->isNotOptional('vat_group') ? $request->vat_group : $service->vat_group;
        $price = $request->isNotOptional('price') ?
            $request->price / (1 + $vat / 100) : $service->price;
        $priceWithVat = $request->isNotOptional('price') ? $request->price : $service->price_with_vat;

        DB::transaction(function () use (
            &$service,
            $data,
            $vat,
            $price,
            $priceWithVat,
        ) {
            // Update service price adjustment if price or vat changed
            if ($service->price !== $price || $service->vat_group !== $vat) {
                PriceAdjustmentService::updatePriceAdjustmentRow($service, $priceWithVat, $vat);
            }

            // Update service
            $service->update([
                ...$data,
                'price' => $price,
                'vat_group' => $vat,
            ]);

            // set name & description
            $service->updateTranslations([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);

            $service->categories()->sync($data['category_ids']);

            if (isset($data['addon_ids'])) {
                $service->addons()->sync($data['addon_ids']);
            }

            if (isset($data['product_ids'])) {
                $service->products()->sync($data['product_ids']);
            }
        });

        UpdateServiceArticleJob::dispatchAfterResponse($service);

        return back()->with('success', __('service updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $hasProducts = $service->products()->exists();

        if ($hasProducts) {
            return back()->with('error', __('service has products'));
        }

        $hasAddons = $service->addons()->exists();

        if ($hasAddons) {
            return back()->with('error', __('service has addons'));
        }

        $hasSchedules = $service->subscriptions()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('schedules', function ($query) {
                    $query->booked();
                })
                    ->orWhereNull('deleted_at');
            })
            ->exists();

        if ($hasSchedules) {
            return back()->with('error', __('service has active schedules or subscriptions'));
        }

        DB::transaction(function () use ($service) {
            // Delete price adjustment rows
            PriceAdjustmentRow::service($service->id)->pending()->delete();

            $service->delete();
        });

        return back()->with('success', __('service deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Service $service): RedirectResponse
    {
        $service->restore();

        return back()->with('success', __('service restored successfully'));
    }
}
