<?php

namespace App\Http\Controllers\Company;

use App\DTOs\Customer\CreateCustomerAddressRequestDTO;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\Customer\UpdateCustomerAddressRequestDTO;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CreateFortnoxCustomerJob;
use App\Jobs\UpdateFortnoxCustomerJob;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Subscription;
use App\Services\Fortnox\FortnoxCustomerService;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Optional;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyAddressController extends BaseUserController
{
    use ResponseTrait;

    /**
     *  Get company addresses
     */
    public function jsonIndex(Customer $company): JsonResponse
    {
        // add validation to check if customer is company and primary contact
        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary()) {
            return $this->errorResponse(
                __('not found'),
                HttpResponse::HTTP_NOT_FOUND
            );
        }

        return $this->successResponse(
            CustomerResponseDTO::transformCollection($company->companyUser->customers, ['address'])
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        Customer $company,
        CreateCustomerAddressRequestDTO $request,
        FortnoxCustomerService $fortnoxCustomerService
    ): RedirectResponse {
        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary()) {
            throw new NotFoundHttpException();
        }

        $shouldCreateInFortnox = false;
        $referenceFromPrimary = false;

        if ($request->isOptional('customer_ref_id')) {
            $existingFortnoxCustomers = $fortnoxCustomerService->getCustomers(
                organisationNumber: $request->identity_number,
            );

            if ($existingFortnoxCustomers->count() > 0) {
                return back()->with('error', __('company already exist in fortnox'));
            }
        }

        $customer = DB::transaction(
            function () use ($request, $company, &$shouldCreateInFortnox, &$referenceFromPrimary) {
                if ($request->isOptional('customer_ref_id')) {
                    $phones = explode(' ', $request->phone1);
                    $dialCode = str_replace('+', '', $phones[0]);

                    // create address
                    $address = Address::create([
                        'city_id' => $request->city_id,
                        'address' => $request->address,
                        'address_2' => $request->isNotOptional('address_2') ? $request->address_2 : '',
                        'area' => $request->isNotOptional('area') ? $request->area : null,
                        'postal_code' => $request->postal_code,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ]);

                    $customer = Customer::create([
                        'address_id' => $address->id,
                        'membership_type' => MembershipTypeEnum::Company(),
                        'type' => ContactTypeEnum::Invoice(), // it's not possible to create primary contact
                        'identity_number' => $request->identity_number,
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone1' => $dialCode.$phones[1],
                        'dial_code' => $dialCode,
                        'due_days' => $request->due_days,
                        'invoice_method' => $request->invoice_method,
                        'reference' => $request->isNotOptional('reference') ? $request->reference : '',
                    ]);

                    $shouldCreateInFortnox = true;
                } else {
                    $customerRef = Customer::find($request->customer_ref_id);
                    $email = $customerRef->email;

                    $customerPrimary = Customer::whereHas('users', function (Builder $query) use ($company) {
                        $query->where('id', $company->companyUser->id);
                    })
                        ->where('type', ContactTypeEnum::Primary())
                        ->first();

                    if ($customerRef->id === $customerPrimary->id) {
                        $email = $request->email;
                        $shouldCreateInFortnox = true;
                        $referenceFromPrimary = true;
                    }

                    $customer = Customer::create([
                        'fortnox_id' => $customerRef->fortnox_id,
                        'customer_ref_id' => $request->customer_ref_id,
                        'address_id' => $customerRef->address_id,
                        'membership_type' => $customerRef->membership_type,
                        'type' => ContactTypeEnum::Invoice(), // it's not possible to create primary contact
                        'identity_number' => $customerRef->identity_number,
                        'name' => $customerRef->name,
                        'email' => $email,
                        'phone1' => $customerRef->phone1,
                        'dial_code' => $customerRef->dial_code,
                        'due_days' => $request->due_days,
                        'invoice_method' => $request->invoice_method,
                        'reference' => $request->reference,
                    ]);
                }

                $customer->users()->attach($company->companyUser->id);

                if ($request->isNotOptional('meta')) {
                    $customer->saveMeta($request->meta);
                }

                return $customer;
            }
        );

        if ($shouldCreateInFortnox && ! $referenceFromPrimary) {
            // create fortnox customer
            CreateFortnoxCustomerJob::dispatchAfterResponse($customer);
        } elseif ($shouldCreateInFortnox && $referenceFromPrimary) {
            // update fortnox customer
            UpdateFortnoxCustomerJob::dispatchAfterResponse($company->companyUser, $customer, $referenceFromPrimary);
        }

        return back()->with('success', __('company address created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Customer $company,
        Customer $customer,
        UpdateCustomerAddressRequestDTO $request,
    ): RedirectResponse {
        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary() ||
            ! $company->companyUser?->customers->contains($customer)) {
            throw new NotFoundHttpException();
        }

        $shouldUpdateInFortnox = false;
        $referenceFromPrimary = false;

        DB::transaction(
            function () use ($request, $customer, $company, &$shouldUpdateInFortnox, &$referenceFromPrimary) {
                if (is_null($customer->customer_ref_id)) {
                    // Normally update it if the customer is not selected from existing customers
                    $phones = $request->isNotOptional('phone1') ? explode(' ', $request->phone1) : [];
                    $dialCode = $request->isNotOptional('phone1') ?
                        str_replace('+', '', $phones[0]) : $customer->dial_code;
                    $phone1 = $request->isNotOptional('phone1') ? $dialCode.$phones[1] : $customer->phone1;

                    $customer->address->update($request->toArray());
                    $customer->update([
                        ...$request->toArray(),
                        'phone1' => $phone1,
                        'dial_code' => $dialCode,
                    ]);

                    // Set fields that are not supposed to be updated for the other customers
                    $request->identity_number = new Optional();
                    $request->reference = new Optional();
                    $request->due_days = new Optional();
                    $request->invoice_method = new Optional();
                    $request->city_id = new Optional();
                    $request->address = new Optional();
                    $request->address_2 = new Optional();
                    $request->area = new Optional();
                    $request->postal_code = new Optional();
                    $request->latitude = new Optional();
                    $request->longitude = new Optional();

                    if ($customer->type === ContactTypeEnum::Primary()) {
                        // Update other customers that reference this customer but different user
                        Customer::where('customer_ref_id', $customer->id)
                            ->whereDoesntHave('users', function (Builder $query) use ($company) {
                                $query->where('id', $company->companyUser->id);
                            })
                            ->update([
                                ...$request->toArray(),
                                'phone1' => $phone1,
                                'dial_code' => $dialCode,
                            ]);

                        // We don't want to update the email of the invoice address of the same user
                        $request->email = new Optional();

                        // Update other customers that share the same user
                        Customer::where('customer_ref_id', $customer->id)
                            ->WhereHas('users', function (Builder $query) use ($company) {
                                $query->where('id', $company->companyUser->id);
                            })
                            ->update([
                                ...$request->toArray(),
                                'phone1' => $phone1,
                                'dial_code' => $dialCode,
                            ]);

                        $customer->companyUser->update([
                            'first_name' => $customer->name,
                            'last_name' => '',
                            'identity_number' => $customer->identity_number,
                            'email' => $customer->email,
                            'cellphone' => $customer->phone1,
                            'dial_code' => $customer->dial_code,
                        ]);
                    } else {
                        // If this customer is not primary, update the other customers that reference this customer
                        Customer::where('customer_ref_id', $customer->id)
                            ->update([
                                ...$request->toArray(),
                                'phone1' => $phone1,
                                'dial_code' => $dialCode,
                            ]);
                    }

                    $shouldUpdateInFortnox = true;
                } else {
                    $email = $customer->email;

                    // Update the email if the reference customer is the primary contact
                    if ($request->isNotOptional('email')) {
                        $customerPrimary = Customer::whereHas('users', function (Builder $query) use ($company) {
                            $query->where('id', $company->companyUser->id);
                        })
                            ->where('type', ContactTypeEnum::Primary())
                            ->first();

                        $referenceFromPrimary = $customer->customer_ref_id === $customerPrimary->id;
                        $shouldUpdateInFortnox = $referenceFromPrimary;
                        $email = $referenceFromPrimary ? $request->email : $email;
                    }

                    $customer->update([
                        'email' => $email,
                        'due_days' => $request->isNotOptional('due_days') ? $request->due_days :
                            $customer->due_days,
                        'invoice_method' => $request->isNotOptional('invoice_method') ? $request->invoice_method :
                            $customer->invoice_method,
                        'reference' => $request->isNotOptional('reference') ? $request->reference :
                            $customer->reference,
                    ]);
                }

                if ($request->isNotOptional('meta')) {
                    $customer->purgeMeta();
                    $customer->saveMeta($request->meta);
                }
            }
        );

        if ($shouldUpdateInFortnox) {
            // update fortnox customer
            UpdateFortnoxCustomerJob::dispatchAfterResponse($company->companyUser, $customer, $referenceFromPrimary);
        }

        return back()->with('success', __('company address updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Customer $company,
        Customer $customer
    ): RedirectResponse {
        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary() ||
            ! $company->companyUser?->customers->contains($customer)) {
            throw new NotFoundHttpException();
        }

        if (is_null($customer->customer_ref_id)) {
            $referenceCustomers = Customer::with('address.city.country', 'companyUser', 'users')
                ->where('customer_ref_id', $customer->id)
                ->get();

            if ($referenceCustomers->count() > 0) {
                return back()->with([
                    'error' => __('customer address is used as reference for other customers'),
                    'errorPayload' => CustomerResponseDTO::transformCollection(
                        $referenceCustomers,
                        includes: ['address.city.country', 'companyUser', 'users'],
                        onlys: [
                            'id',
                            'customerRefId',
                            'reference',
                            'identityNumber',
                            'phone1',
                            'formattedPhone1',
                            'email',
                            'type',
                            'name',
                            'membershipType',
                            'dueDays',
                            'invoiceMethod',
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
                            'companyUser.id',
                            'users.id',
                        ],
                    ),
                ]);
            }
        }

        $exists = Subscription::withTrashed()
            ->where('customer_id', $customer->id)
            ->where(function (Builder $query) {
                $query->whereHas('schedules', function (Builder $query) {
                    $query->booked();
                })
                    ->orWhereNull('deleted_at');
            })
            ->exists();

        if ($exists) {
            return back()->with(
                'error',
                __('still used in schedules or subscriptions')
            );
        }

        $customer->delete();

        return back()->with('success', __('company address deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(
        Customer $company,
        Customer $customer
    ): RedirectResponse {
        $user = $company->companyUser;
        $customers = $user ? $user->customers()->withTrashed()->get() :
            Collection::make();

        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary() ||
            ! $customers->contains($customer)) {
            throw new NotFoundHttpException();
        }

        $customer->restore();

        return back()->with('success', __('company address restored successfully'));
    }
}
