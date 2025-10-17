<?php

namespace App\Http\Controllers\Employee;

use App\DTOs\Address\CountryResponseDTO;
use App\DTOs\User\UserEmployeeWizardRequestDTO;
use App\Events\EmployeeCreated;
use App\Http\Controllers\User\BaseUserController;
use App\Models\Address;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Store;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Str;

class EmployeeWizardController extends BaseUserController
{
    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $notAllowedRoles = [
            'Superadmin',
            'Customer',
            'Company',
            'Employee',
        ];
        $roles = Role::whereNotIn('name', $notAllowedRoles)->get()->pluck('name')->toArray();

        return Inertia::render('Employee/Wizard/index', [
            'countries' => $this->getCountries(),
            'roles' => $roles,
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
     * Store resource in storage.
     * In this case we are creating a employee data for fortnox,
     * address and a property as well.
     */
    public function store(UserEmployeeWizardRequestDTO $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $phones = explode(' ', $request->cellphone);
            $dialCode = str_replace('+', '', $phones[0]);

            // create user
            $user = $this->createUser([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'cellphone' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
                'password' => Str::random(12),
                'identity_number' => $request->identity_number,
            ], array_merge($request->roles, ['Employee']));

            // create address
            $address = Address::create([
                'city_id' => $request->city_id,
                'address' => $request->address,
                'address_2' => $request->isNotOptional('address_2') ? $request->address_2 : null,
                'area' => $request->isNotOptional('area') ? $request->area : null,
                'postal_code' => $request->postal_code,
                'latitude' => $request->isNotOptional('latitude') ? $request->latitude : null,
                'longitude' => $request->isNotOptional('longitude') ? $request->longitude : null,
            ]);

            $user->info()->create([
                'avatar' => $request->isNotOptional('avatar') ? $request->avatar : null,
                'language' => $request->language,
                'timezone' => $request->timezone,
                'currency' => $request->currency,
                'two_factor_auth' => $request->two_factor_auth,
                'marketing' => 0,
            ]);

            // create fortnox employee
            $employee = Employee::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'identity_number' => $request->identity_number,
                'name' => $user->full_name,
                'email' => $user->email,
                'phone1' => $user->cellphone,
                'dial_code' => $user->dial_code,
            ]);

            if ($request->isNotOptional('meta')) {
                $employee->saveMeta($request->meta);
            }

            // Assign to stores if user superadmin
            if (in_array('Superadmin', $request->roles)) {
                $user->stores()->sync(Store::all()->pluck('id')->toArray());
            }

            return $user;
        });

        // dispatch employee created event
        EmployeeCreated::dispatch($user);

        return back()->with('success', __('employee created successfully'));
    }
}
