<?php

namespace App\Http\Controllers\Property;

use App\DTOs\Address\CountryResponseDTO;
use App\DTOs\Property\PropertyWizardDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\MembershipTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Models\Address;
use App\Models\Country;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\User;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PropertyWizardController extends BaseUserController
{
    /**
     * Display the index view.
     */
    public function index(): Response
    {
        return Inertia::render('Property/Wizard/index', [
            'countries' => $this->getCountries(),
            'customers' => $this->getCustomerss(),
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

    private function getCustomerss()
    {
        $onlys = [
            'id',
            'fullname',
        ];
        $companies = User::selectWithRelations($onlys)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Customer');
            })
            ->whereHas('customers', function ($query) {
                $query->where('membership_type', MembershipTypeEnum::Private());
            })
            ->get();

        return UserResponseDTO::collection($companies)->only(...$onlys);
    }

    /**
     * Store resource in storage.
     */
    public function store(PropertyWizardDTO $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            // create address
            $address = Address::create([
                'city_id' => $request->city_id,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
                'latitude' => $request->isNotOptional('latitude') ? $request->latitude : null,
                'longitude' => $request->isNotOptional('longitude') ? $request->longitude : null,
            ]);

            // create property
            $property = Property::create([
                'address_id' => $address->id,
                'area' => $request->isNotOptional('area') ? $request->area : null,
                'property_type_id' => 1,
                'membership_type' => MembershipTypeEnum::Private(),
                'square_meter' => $request->square_meter,
                'key_information' => $request->isNotOptional('key_information') ?
                    $request->key_information : null,
            ]);
            $property->users()->attach($request->user_id);

            if ($request->isNotOptional('meta')) {
                $property->saveMeta(array_keys_to_snake_case($request->meta));
            }

            // update key place
            if ($request->isNotOptional('key_information')
                && $request->key_information->isNotOptional('key_place')
                && $request->key_information->key_place) {
                KeyPlace::where('id', $request->key_information->key_place)
                    ->update(['property_id' => $property->id]);
                KeyPlace::createKeyPlaceIfFull();
            }
        });

        return back()->with('success', __('property created successfully'));
    }
}
