<?php

namespace App\Http\Controllers\CashierCustomer;

use App\DTOs\Address\CountryResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CashierCustomerController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view.
     */
    public function wizard(): Response
    {
        return Inertia::render('CashierCustomer/Wizard/index', [
            'countries' => $this->getCountries(),
            'dueDays' => get_setting(GlobalSettingEnum::InvoiceDueDays(), 30),
        ]);
    }

    private function getCountries()
    {
        $onlys = [
            'id',
            'name',
        ];
        $countries = Country::selectWithRelations($onlys)->get();

        return CountryResponseDTO::collection($countries)->only(...$onlys);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            defaultFilter: [
                'roles_name_in' => 'Customer,Company',
                'isCompanyContact_eq' => false,
            ],
        );
        $paginatedData = User::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            UserResponseDTO::transformCollection($paginatedData->data),
        );
    }
}
