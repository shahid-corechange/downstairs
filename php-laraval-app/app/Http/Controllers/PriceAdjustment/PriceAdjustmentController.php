<?php

namespace App\Http\Controllers\PriceAdjustment;

use App\DTOs\PriceAdjustment\CreatePriceAdjustmentRequestDTO;
use App\DTOs\PriceAdjustment\PriceAdjustmentResponseDTO;
use App\Enums\PriceAdjustment\PriceAdjustmentPriceTypeEnum;
use App\Enums\PriceAdjustment\PriceAdjustmentTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\PriceAdjustment;
use App\Models\PriceAdjustmentRow;
use App\Services\PriceAdjustmentService;
use DB;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PriceAdjustmentController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'causer',
        'rows',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'type',
        'status',
        'description',
        'priceType',
        'price',
        'executionDate',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'causer.fullname',
        'rows.id',
        'rows.adjustableId',
        'rows.adjustableName',
        'rows.previousPrice',
        'rows.priceWithVat',
        'rows.status',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            pagination: 'page',
            sort: ['created_at' => 'desc'],
        );
        $paginatedData = PriceAdjustment::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('PriceAdjustment/Overview/index', [
            'priceAdjustments' => PriceAdjustmentResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = PriceAdjustment::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            PriceAdjustmentResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Create new price adjustment.
     */
    public function store(CreatePriceAdjustmentRequestDTO $request)
    {
        if ($request->type === PriceAdjustmentTypeEnum::FixedPrice() &&
            $request->price_type !== PriceAdjustmentPriceTypeEnum::DynamicPercentage()
        ) {
            return back()->with('error', __('fixed price only support dynamic percentage'));
        }

        $model = PriceAdjustmentService::getModel($request->type);

        DB::transaction(function () use ($request, $model) {
            $priceAdjustment = PriceAdjustment::create([
                ...$request->toArray(),
                'causer_id' => auth()->id(),
            ]);

            foreach ($request->row_ids as $id) {
                $adjustable = $model::findOrFail($id);

                $previousPrice = PriceAdjustmentService::getPreviousPriceWithVat($adjustable);
                $vat = PriceAdjustmentService::getVat($adjustable);

                PriceAdjustmentRow::create([
                    'price_adjustment_id' => $priceAdjustment->id,
                    'adjustable_id' => $id,
                    'adjustable_type' => $model,
                    'previous_price' => $previousPrice,
                    'price' => PriceAdjustmentService::calculateNewBasePrice(
                        $request->price_type,
                        $previousPrice,
                        $request->price,
                        $vat,
                        $adjustable,
                    ),
                    'vat_group' => $vat,
                ]);
            }
        });

        return back()->with('success', __('price adjustment created successfully'));
    }

    /**
     * Update the specified price adjustment.
     */
    public function update(
        PriceAdjustment $priceAdjustment,
        CreatePriceAdjustmentRequestDTO $request
    ) {
        if ($request->type === PriceAdjustmentTypeEnum::FixedPrice() &&
            $request->price_type !== PriceAdjustmentPriceTypeEnum::DynamicPercentage()
        ) {
            return back()->with('error', __('fixed price only support dynamic percentage'));
        }

        $model = PriceAdjustmentService::getModel($request->type);

        DB::transaction(function () use ($request, $priceAdjustment, $model) {
            $oldType = $priceAdjustment->type;
            $oldPriceType = $priceAdjustment->price_type;
            $oldPrice = $priceAdjustment->price;

            $priceAdjustment->update([
                ...$request->toArray(),
                'causer_id' => auth()->id(),
            ]);

            // Delete rows that not in the row ids
            $priceAdjustment->rows()->whereNotIn('adjustable_id', $request->row_ids)->delete();

            $rowIds = $priceAdjustment->rows->pluck('adjustable_id')->toArray();
            // Row from the request that not in the row ids
            $newRowIds = array_diff($request->row_ids, $rowIds);

            // Update rows if the price type or price is changed
            if ($oldPriceType !== $request->price_type ||
                $oldPrice !== $request->price ||
                $oldType !== $request->type) {
                $priceAdjustment->rows->each(function ($row) use ($request, $model) {
                    $row->update([
                        'adjustable_type' => $model,
                        'price' => PriceAdjustmentService::calculateNewBasePrice(
                            $request->price_type,
                            $row->previous_price_with_vat,
                            $request->price,
                            $row->vat_group,
                            $row->adjustable,
                        ),
                    ]);
                });
            }

            // Create rows that not in the row ids
            foreach ($newRowIds as $id) {
                $adjustable = $model::findOrFail($id);

                $previousPrice = PriceAdjustmentService::getPreviousPriceWithVat($adjustable);
                $vat = PriceAdjustmentService::getVat($adjustable);

                PriceAdjustmentRow::create([
                    'price_adjustment_id' => $priceAdjustment->id,
                    'adjustable_id' => $id,
                    'adjustable_type' => $model,
                    'previous_price' => $previousPrice,
                    'price' => PriceAdjustmentService::calculateNewBasePrice(
                        $request->price_type,
                        $previousPrice,
                        $request->price,
                        $vat,
                        $adjustable,
                    ),
                    'vat_group' => $vat,
                ]);
            }
        });

        return back()->with('success', __('price adjustment updated successfully'));
    }

    /**
     * Remove the specified price adjustment.
     */
    public function destroy(PriceAdjustment $priceAdjustment)
    {
        $priceAdjustment->delete();

        return back()->with('success', __('price adjustment deleted successfully'));
    }
}
