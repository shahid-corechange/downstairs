<?php

namespace App\Http\Controllers\Store;

use App\DTOs\Store\CreateStoreRequestDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\DTOs\Store\UpdateStoreRequestDTO;
use App\DTOs\User\UserResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Address;
use App\Models\Store;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'address.city.country',
        'users',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'addressId',
        'name',
        'companyNumber',
        'phone',
        'email',
        'formattedPhone',
        'deletedAt',
        'address.address',
        'address.address2',
        'address.fullAddress',
        'address.postalCode',
        'address.latitude',
        'address.longitude',
        'address.cityId',
        'address.city.name',
        'address.city.countryId',
        'address.city.country.name',
        'users.id',
        'users.fullname',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(size: -1, show: 'all');
        $paginatedData = Store::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Store/Overview/index', [
            'stores' => StoreResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'employees' => $this->getEmployees(),
        ]);
    }

    private function getEmployees()
    {
        $onlys = [
            'id',
            'fullname',
        ];

        $employees = User::selectWithRelations($onlys)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Superadmin', 'Cashier']);
            })
            ->get();

        return UserResponseDTO::collection($employees)->only(...$onlys);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Store::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            StoreResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Display the show view as json.
     */
    public function jsonShow(int $storeId): JsonResponse
    {
        $data = Store::selectWithRelations(mergeFields: true)
            ->findOrFail($storeId);

        return $this->successResponse(
            StoreResponseDTO::transformData($data),
        );
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateStoreRequestDTO $request): RedirectResponse
    {
        $data = $request->toArray();
        // Must include superadmin in the store users
        $superadmin = User::role('Superadmin')->get()->pluck('id')->toArray();
        $userIds = array_merge($superadmin, $data['user_ids']);

        DB::transaction(function () use ($data, $userIds) {
            $address = Address::create($data);

            $phones = explode(' ', $data['phone']);
            $dialCode = str_replace('+', '', $phones[0]);

            $store = Store::create([
                ...$data,
                'phone' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
                'address_id' => $address->id,
            ]);

            $store->users()->sync($userIds);
        });

        return back()->with('success', __('store created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateStoreRequestDTO $request,
        Store $store
    ): RedirectResponse {
        $data = $request->toArray();

        $phones = $request->isNotOptional('phone') ? explode(' ', $request->phone) : [];
        $dialCode = $request->isNotOptional('phone') ? str_replace('+', '', $phones[0]) : $store->dial_code;
        $superadmin = User::role('Superadmin')->get()->pluck('id')->toArray();

        // Check if superadmin remove from the request
        $userIds = $data['user_ids'];
        if (count(array_intersect($superadmin, $userIds)) !== count($superadmin)) {
            return back()->with('error', __('superadmin cannot be removed from the store'));
        }

        DB::transaction(function () use ($data, $phones, $dialCode, $store) {
            $store->update([
                ...$data,
                'phone' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
            ]);

            $store->address->update($data);
            $store->users()->sync($data['user_ids']);
        });

        return back()->with('success', __('store updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store): RedirectResponse
    {
        $store->delete();

        return back()->with('success', __('store deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Store $store): RedirectResponse
    {
        $store->restore();

        return back()->with('success', __('store restored successfully'));
    }
}
