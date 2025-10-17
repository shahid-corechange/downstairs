<?php

namespace App\Http\Controllers\Service;

use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\ServiceQuarter\CreateServiceQuarterRequestDTO;
use App\DTOs\ServiceQuarter\ServiceQuarterResponseDTO;
use App\DTOs\ServiceQuarter\UpdateServiceQuarterRequestDTO;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\Service;
use App\Models\ServiceQuarter;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceQuarterController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'service',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'serviceId',
        'minSquareMeters',
        'maxSquareMeters',
        'quarters',
        'hours',
        'createdAt',
        'updatedAt',
        'service.name',
        'service.translations',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            sort: ['service_id' => 'asc', 'min_square_meters' => 'asc'],
            pagination: 'page',
            show: 'all'
        );

        $paginatedData = ServiceQuarter::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Service/Quarter/index', [
            'serviceQuarters' => ServiceQuarterResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'services' => $this->getServices(),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    private function getServices()
    {
        $services = Service::whereNotIn('id', [2, 4])->get();

        return ServiceResponseDTO::transformCollection($services);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = ServiceQuarter::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ServiceQuarterResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateServiceQuarterRequestDTO $request): RedirectResponse
    {
        // validation to make sure service quarter not overlap with other service quarter
        $count = ServiceQuarter::where('service_id', $request->service_id)
            ->whereNot(function (Builder $query) use ($request) {
                $query->where('max_square_meters', '<', $request->min_square_meters)
                    ->orWhere('min_square_meters', '>', $request->max_square_meters);
            })
            ->count();

        if ($count > 0) {
            return back()->with('error', __(
                'service quarter overlap',
                ['action' => __('create action')]
            ));
        }

        DB::transaction(function () use ($request) {
            ServiceQuarter::create($request->toArray());
        });

        return back()->with('success', __('service quarter created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateServiceQuarterRequestDTO $request,
        ServiceQuarter $serviceQuarter,
    ): RedirectResponse {
        $min = $request->isNotOptional('min_square_meters') ?
            $request->min_square_meters : $serviceQuarter->min_square_meters;
        $max = $request->isNotOptional('max_square_meters') ?
            $request->max_square_meters : $serviceQuarter->max_square_meters;

        // validation to make sure service quarter not overlap with other service quarter
        $count = ServiceQuarter::where('service_id', $serviceQuarter->service_id)
            ->whereNot('id', $serviceQuarter->id)
            ->whereNot(function (Builder $query) use ($min, $max) {
                $query->where('max_square_meters', '<', $min)
                    ->orWhere('min_square_meters', '>', $max);
            })
            ->count();

        if ($count > 0) {
            return back()->with('error', __(
                'service quarter overlap',
                ['action' => __('update action')]
            ));
        }

        DB::transaction(function () use ($request, $min, $max, $serviceQuarter) {
            $serviceQuarter->update([
                'min_square_meters' => $min,
                'max_square_meters' => $max,
                'quarters' => $request->isNotOptional('quarters') ?
                    $request->quarters : $serviceQuarter->quarters,
            ]);
        });

        return back()->with('success', __('service quarter updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceQuarter $serviceQuarter): RedirectResponse
    {
        $serviceQuarter->delete();

        return back()->with('success', __('service quarter deleted successfully'));
    }
}
