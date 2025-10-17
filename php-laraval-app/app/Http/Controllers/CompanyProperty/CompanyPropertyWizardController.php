<?php

namespace App\Http\Controllers\CompanyProperty;

use App\DTOs\Address\CountryResponseDTO;
use App\DTOs\Property\CompanyPropertyWizardDTO;
use App\DTOs\Property\PropertyTypeResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Models\Address;
use App\Models\Country;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyPropertyWizardController extends BaseUserController
{
    /**
     * Display the index view.
     */
    public function index(): Response
    {
        return Inertia::render('CompanyProperty/Wizard/index', [
            'countries' => $this->getCountries(),
            'companies' => $this->getCompanies(),
            'propertyTypes' => $this->getPropertyTypes(),
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

    private function getPropertyTypes()
    {
        $onlys = [
            'id',
            'name',
        ];
        $propertyTypes = PropertyType::selectWithRelations($onlys)
            ->whereNot('id', 1)
            ->get();

        return PropertyTypeResponseDTO::collection($propertyTypes)->only(...$onlys);
    }

    private function getCompanies()
    {
        $onlys = [
            'id',
            'fullname',
        ];
        $companies = User::selectWithRelations($onlys)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Company');
            })
            ->whereHas('customers', function ($query) {
                $query->where('membership_type', MembershipTypeEnum::Company());
            })
            ->get();

        return UserResponseDTO::collection($companies)->only(...$onlys);
    }

    /**
     * Store resource in storage.
     */
    public function store(CompanyPropertyWizardDTO $request): RedirectResponse
    {
        $user = User::find($request->user_id);

        if (! $user || ! $user->hasRole('Company')) {
            throw new NotFoundHttpException(__('company not found'));
        }

        /** @var \App\Models\Customer|null */
        $company = $user->customers()->where('type', ContactTypeEnum::Primary())
            ->where('membership_type', MembershipTypeEnum::Company())
            ->first();

        if (! $company) {
            throw new NotFoundHttpException(__('company not found'));
        }

        $companyContactIds = $company->company_contact_users->pluck('id')->toArray();

        DB::transaction(function () use ($request, $companyContactIds) {
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
                'property_type_id' => $request->property_type_id,
                'membership_type' => MembershipTypeEnum::Company(),
                'square_meter' => $request->square_meter,
                'key_information' => $request->isNotOptional('key_information') ?
                    $request->key_information : null,
            ]);
            $property->users()->syncWithoutDetaching([$request->user_id, ...$companyContactIds]);

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
