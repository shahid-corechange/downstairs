<?php

namespace App\Http\Controllers\CompanyCustomerDiscount;

use App\DTOs\CustomerDiscount\CreateCustomerDiscountRequestDTO;
use App\DTOs\CustomerDiscount\CustomerDiscountResponseDTO;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\CustomerDiscount;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyCustomerDiscountController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'userId',
        'user.fullname',
        'type',
        'value',
        'startDate',
        'endDate',
        'usageLimit',
        'isActive',
        'createdAt',
        'updatedAt',
        'deletedAt',
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

        $paginatedData = CustomerDiscount::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Company/Discount/index', [
            'customerDiscounts' => CustomerDiscountResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'customerDiscountTypes' => enum_to_options(CustomerDiscountTypeEnum::values()),
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
        $paginatedData = CustomerDiscount::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CustomerDiscountResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateCustomerDiscountRequestDTO $request): RedirectResponse
    {
        // validation to make sure discount not overlap with other discount
        $customerDiscounts = CustomerDiscount::where('user_id', $request->user_id)
            ->where('type', $request->type)
            ->period($request->start_date, $request->end_date)
            ->count();

        if ($customerDiscounts > 0) {
            return back()->with('error', __(
                'company discount overlap with other discounts',
                ['action' => __('create action')]
            ));
        }

        DB::transaction(function () use ($request) {
            CustomerDiscount::create($request->toArray());
        });

        return back()->with('success', __('company discount created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        CreateCustomerDiscountRequestDTO $request,
        CustomerDiscount $customerDiscount
    ): RedirectResponse {
        // validation to make sure discount not overlap with other discount
        $customerDiscounts = CustomerDiscount::where('user_id', $request->user_id)
            ->where('id', '!=', $customerDiscount->id)
            ->where('type', $request->type)
            ->period($request->start_date, $request->end_date)
            ->count();

        if ($customerDiscounts > 0) {
            return back()->with('error', __(
                'company discount overlap with other discounts',
                ['action' => __('update action')]
            ));
        }

        DB::transaction(function () use ($request, $customerDiscount) {
            $customerDiscount->update([
                'user_id' => $request->user_id,
                'type' => $request->type,
                'value' => $request->value,
                'start_date' => $request->isNotOptional('start_date') ?
                    $request->start_date : $customerDiscount->start_date,
                'end_date' => $request->isNotOptional('end_date') ?
                    $request->end_date : $customerDiscount->end_date,
                'usage_limit' => $request->isNotOptional('usage_limit') ?
                    $request->usage_limit : $customerDiscount->usage_limit,
            ]);
        });

        return back()->with('success', __('company discount updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerDiscount $customerDiscount): RedirectResponse
    {
        $customerDiscount->delete();

        return back()->with('success', __('company discount deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(CustomerDiscount $customerDiscount): RedirectResponse
    {
        // validation to make sure discount not overlap with other discount
        $customerDiscounts = CustomerDiscount::where('user_id', $customerDiscount->user_id)
            ->where('id', '!=', $customerDiscount->id)
            ->where('type', $customerDiscount->type)
            ->period($customerDiscount->start_date, $customerDiscount->end_date)
            ->count();

        if ($customerDiscounts > 0) {
            return back()->with('error', __(
                'company discount overlap with other discounts',
                ['action' => __('restore action')]
            ));
        }

        $customerDiscount->restore();

        return back()->with('success', __('company discount restored successfully'));
    }
}
