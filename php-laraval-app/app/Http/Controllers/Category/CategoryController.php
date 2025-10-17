<?php

namespace App\Http\Controllers\Category;

use App\Contracts\StorageService;
use App\DTOs\Category\CategoryResponseDTO;
use App\DTOs\Category\CreateCategoryRequestDTO;
use App\DTOs\Category\UpdateCategoryRequestDTO;
use App\Enums\Azure\BlobStorage\BlobStorageContainerEnum;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use ResponseTrait;

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'name',
        'thumbnailImage',
        'description',
        'deletedAt',
        'translations',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(size: -1, show: 'all');
        $paginatedData = Category::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Category/Overview/index', [
            'categories' => CategoryResponseDTO::transformCollection(
                $paginatedData->data,
                onlys: $this->onlys,
            ),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Category::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CategoryResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Store a new category.
     */
    public function store(CreateCategoryRequestDTO $request, StorageService $storage): RedirectResponse
    {
        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            $filename = generate_filename('category', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['thumbnail_image'] = $url;
        }

        DB::transaction(function () use ($data) {
            $category = Category::create($data);

            // set name & description
            $category->translations()->createMany([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);
        });

        return back()->with('success', __('category created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateCategoryRequestDTO $request,
        Category $category,
        StorageService $storage
    ): RedirectResponse {
        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            if ($category->thumbnail_image) {
                $oldFilename = basename($category->thumbnail_image);
                $storage->delete(BlobStorageContainerEnum::Images(), $oldFilename);
            }

            $filename = generate_filename('category', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['thumbnail_image'] = $url;
        }

        DB::transaction(function () use ($category, $data) {
            $category->update($data);

            // set name & description
            $category->updateTranslations([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);
        });

        return back()->with('success', __('category updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if (in_array($category->id, config('downstairs.categories.systemIds'))) {
            return back()->with('error', __('system category cannot be deleted'));
        }

        $category->delete();

        return back()->with('success', __('category deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Category $category): RedirectResponse
    {
        $category->restore();

        return back()->with('success', __('category restored successfully'));
    }
}
