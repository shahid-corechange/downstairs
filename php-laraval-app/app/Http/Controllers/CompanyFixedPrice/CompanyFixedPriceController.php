<?php

namespace App\Http\Controllers\CompanyFixedPrice;

use App\DTOs\FixedPrice\CreateFixedPriceRequestDTO;
use App\DTOs\FixedPrice\FixedPriceResponseDTO;
use App\DTOs\FixedPrice\UpdateFixedPriceRequestDTO;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\MetaTrait;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CreateOrderFixedPriceJob;
use App\Models\FixedPrice;
use App\Models\Subscription;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyFixedPriceController extends BaseUserController
{
    use MetaTrait;
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user',
        'laundryProducts',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'userId',
        'type',
        'startDate',
        'endDate',
        'hasActiveSubscriptions',
        'isActive',
        'isPerOrder',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'user.fullname',
        'laundryProducts.id',
        'laundryProducts.name',
        'laundryProducts.price',
        'laundryProducts.priceWithVat',
        'laundryProducts.vatGroup',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            [
                'user_roles_name_in' => 'Company',
            ],
            defaultFilter: [
                'isActive_eq' => 'true',
            ],
            sort: ['deleted_at' => 'asc', 'created_at' => 'desc'],
            pagination: 'page',
            show: 'all'
        );

        $paginatedData = FixedPrice::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Company/FixedPrice/index', [
            'fixedPrices' => FixedPriceResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            [
                'user_roles_name_in' => 'Company',
            ],
        );
        $paginatedData = FixedPrice::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            FixedPriceResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Display the show view as json.
     */
    public function jsonShow(int $fixedPriceId): JsonResponse
    {
        $data = FixedPrice::selectWithRelations(mergeFields: true)
            ->findOrFail($fixedPriceId);

        return $this->successResponse(
            FixedPriceResponseDTO::transformData($data),
        );
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateFixedPriceRequestDTO $request): RedirectResponse
    {
        $laundryTypes = [FixedPriceTypeEnum::Laundry(), FixedPriceTypeEnum::CleaningAndLaundry()];

        if (in_array($request->type, $laundryTypes) && ! $request->rows->toCollection()->contains(
            fn ($row) => $row->type === FixedPriceRowTypeEnum::Laundry()
        )) {
            return back()->with('error', __('can not create fixed price without laundry row'));
        }

        if (in_array($request->type, [FixedPriceTypeEnum::Cleaning(), FixedPriceTypeEnum::CleaningAndLaundry()]) &&
        ! $request->rows->toCollection()->contains(fn ($row) => $row->type === FixedPriceRowTypeEnum::Service())) {
            return back()->with('error', __('can not create fixed price without service row'));
        }

        if (in_array($request->type, $laundryTypes)) {
            $isExist = FixedPrice::where('user_id', $request->user_id)
                ->whereIn('type', $laundryTypes)
                ->exists();

            if ($isExist) {
                return back()->with('error', __('can not create laundry fixed price more than one'));
            }
        }

        if (! $request->is_per_order) {
            $types = array_unique([$request->type, FixedPriceTypeEnum::CleaningAndLaundry()]);
            $isMonthlyExist = FixedPrice::where('user_id', $request->user_id)
                ->whereIn('type', $types)
                ->where('is_per_order', false)
                ->exists();

            if ($isMonthlyExist) {
                return back()->with(
                    'error',
                    __('customer can only have one monthly fixed price', ['type' => strtolower(__($request->type))])
                );
            }
        }

        /**
         * Check if given subscription already have fixed price.
         */
        $countSubscription = Subscription::whereIn('id', $request->subscription_ids)
            ->whereNotNull('fixed_price_id')
            ->count();

        if ($countSubscription > 0) {
            return back()->with(
                'error',
                __('there are subscription that already have fixed price', ['count' => $countSubscription])
            );
        }

        $types = $request->rows->toCollection()->pluck('type');

        /**
         * Check if there is duplicate row type.
         */
        if (count($types) > count($types->unique())) {
            return back()->with('error', __('fixed price row type must be unique'));
        }

        $rows = $request->rows->toCollection()->map(
            fn ($row) => [
                'type' => $row->type,
                'quantity' => $row->quantity,
                'price' => $row->price / (1 + $row->vat_group / 100),
                'vat_group' => $row->vat_group,
                'has_rut' => false,
            ]
        );

        DB::transaction(function () use ($request, $rows, $laundryTypes) {
            $fixedPrice = FixedPrice::create([
                ...$request->toArray(),
                'start_date' => $request->isOptional('start_date') ? null : $request->start_date,
                'end_date' => $request->isOptional('end_date') ? null : $request->end_date,
                'is_per_order' => $request->is_per_order,
            ]);

            Subscription::where('user_id', $request->user_id)
                ->whereIn('id', $request->subscription_ids)
                ->update(['fixed_price_id' => $fixedPrice->id]);

            $fixedPrice->rows()->createMany($rows);
            if (in_array($request->type, $laundryTypes) && $request->laundry_product_ids) {
                $fixedPrice->laundryProducts()->attach($request->laundry_product_ids);
            }

            CreateOrderFixedPriceJob::dispatchAfterResponse($fixedPrice, false);
        });

        return back()->with('success', __('fixed price created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFixedPriceRequestDTO $request, FixedPrice $fixedPrice): RedirectResponse
    {
        /**
         * Check if given subscription already have fixed price.
         */
        $countSubscription = Subscription::whereIn('id', $request->subscription_ids)
            ->where('fixed_price_id', '!=', $fixedPrice->id)
            ->whereNotNull('fixed_price_id')
            ->count();

        if ($countSubscription > 0) {
            return back()->with(
                'error',
                __('there are subscription that already have fixed price', ['count' => $countSubscription])
            );
        }

        if (! $request->is_per_order) {
            $isMonthlyExist = FixedPrice::where('user_id', $fixedPrice->user_id)
                ->where('type', $fixedPrice->type)
                ->where('is_per_order', false)
                ->where('id', '!=', $fixedPrice->id)
                ->exists();

            if ($isMonthlyExist) {
                return back()->with(
                    'error',
                    __('customer can only have one monthly fixed price', ['type' => strtolower(__($fixedPrice->type))])
                );
            }
        }

        if ($fixedPrice->type !== FixedPriceTypeEnum::Laundry() && ! $request->subscription_ids) {
            return back()->with('error', __('subscriptions can not be empty'));
        }

        DB::transaction(function () use ($request, $fixedPrice) {
            $isPerOrderChanged = $fixedPrice->is_per_order !== $request->is_per_order;
            $fixedPrice->update([
                'start_date' => $request->isOptional('start_date') ? $fixedPrice->start_date : $request->start_date,
                'end_date' => $request->isOptional('end_date') ? $fixedPrice->end_date : $request->end_date,
                'is_per_order' => $request->is_per_order,
            ]);

            Subscription::where('user_id', $fixedPrice->user_id)
                ->whereIn('id', $request->subscription_ids)
                ->update(['fixed_price_id' => $fixedPrice->id]);
            Subscription::where('user_id', $fixedPrice->user_id)
                ->whereNotIn('id', $request->subscription_ids)
                ->where('fixed_price_id', $fixedPrice->id)
                ->update(['fixed_price_id' => null]);

            CreateOrderFixedPriceJob::dispatchAfterResponse($fixedPrice, $isPerOrderChanged);
        });

        return back()->with('success', __('fixed price updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FixedPrice $fixedPrice): RedirectResponse
    {
        DB::transaction(function () use ($fixedPrice) {
            $fixedPrice->subscriptions()->withTrashed()->update(['fixed_price_id' => null]);
            $fixedPrice->delete();
        });

        return back()->with('success', __('fixed price deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(FixedPrice $fixedPrice): RedirectResponse
    {
        $laundryTypes = [FixedPriceTypeEnum::Laundry(), FixedPriceTypeEnum::CleaningAndLaundry()];

        if (in_array($fixedPrice->type, $laundryTypes)) {
            $isExist = FixedPrice::where('user_id', $fixedPrice->user_id)
                ->whereIn('type', $laundryTypes)
                ->exists();

            if ($isExist) {
                return back()->with('error', __('laundry fixed price already exists'));
            }
        }

        if (! $fixedPrice->is_per_order) {
            $isMonthlyExist = FixedPrice::where('user_id', $fixedPrice->user_id)
                ->where('type', $fixedPrice->type)
                ->where('is_per_order', false)
                ->where('id', '!=', $fixedPrice->id)
                ->exists();

            if ($isMonthlyExist) {
                return back()->with(
                    'error',
                    __('customer can only have one monthly fixed price', ['type' => strtolower(__($fixedPrice->type))])
                );
            }
        }

        DB::transaction(function () use ($fixedPrice) {
            $fixedPrice->restore();
        });

        return back()->with('success', __('fixed price restored successfully'));
    }
}
