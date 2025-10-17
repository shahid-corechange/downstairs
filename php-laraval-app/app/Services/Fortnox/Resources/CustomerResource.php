<?php

namespace App\Services\Fortnox\Resources;

use App\DTOs\Fortnox\Customer\CreateFortnoxCustomerRequestDTO;
use App\DTOs\Fortnox\Customer\CustomerDTO;
use App\DTOs\Fortnox\Customer\UpdateFortnoxCustomerRequestDTO;
use App\Exceptions\OperationFailedException;
use App\Models\Customer;
use Illuminate\Http\Response;
use Str;

trait CustomerResource
{
    /**
     * Get list of customers.
     *
     * @return \Spatie\LaravelData\DataCollection<array-key,\App\DTOs\Fortnox\Customer\CustomerDTO>
     */
    public function getCustomers(
        int $limit = 100,
        string $filter = null,
        string $customerNumber = null,
        string $name = null,
        string $zipcode = null,
        string $city = null,
        string $email = null,
        string $phone = null,
        string $organisationNumber = null,
        string $gln = null,
        string $glnDelivery = null,
        string $lastModified = null,
        string $sortBy = null,
    ) {
        $query = [
            'limit' => $limit,
            'filter' => $filter,
            'customernumber' => $customerNumber,
            'name' => $name,
            'zipcode' => $zipcode,
            'city' => $city,
            'email' => $email,
            'phone' => $phone,
            'organisationnumber' => $organisationNumber,
            'gln' => $gln,
            'glndelivery' => $glnDelivery,
            'lastmodified' => $lastModified,
            'sortby' => $sortBy,
        ];
        $response = $this->sendRequest('customers', 'GET', query: $query);

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                $query = array_filter($query, function ($value) {
                    return $value !== null;
                });

                throw new OperationFailedException(
                    __(
                        'failed to get customers',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode($query),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return CustomerDTO::collection($response->json('Customers'));
    }

    public function deleteCustomer(string $customerNumber): void
    {
        $response = $this->sendRequest("customers/{$customerNumber}", 'DELETE');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_NO_CONTENT) {
            throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
        }
    }

    public function createCustomer(CreateFortnoxCustomerRequestDTO $data): CustomerDTO
    {
        $response = $this->sendRequest(
            'customers',
            'POST',
            body: ['Customer' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create customer',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'customer_number' => $data->customer_number,
                                'name' => $data->name,
                                'city' => $data->city,
                                'email' => $data->email,
                                'phone1' => $data->phone1,
                                'type' => $data->type,
                                'email_invoice' => $data->email_invoice,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return CustomerDTO::from($response->json('Customer'));
    }

    public function updateCustomer(string $customerId, UpdateFortnoxCustomerRequestDTO $data): CustomerDTO
    {
        $response = $this->sendRequest(
            "customers/{$customerId}",
            'PUT',
            body: ['Customer' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException($response->json());
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update customer',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'customer_id' => $customerId,
                                'customer_number' => $data->customer_number,
                                'name' => $data->name,
                                'city' => $data->city,
                                'email' => $data->email,
                                'phone1' => $data->phone1,
                                'email_invoice' => $data->email_invoice,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return CustomerDTO::from($response->json('Customer'));
    }

    /**
     * Sync existing customer without fortnox_id to Fortnox.
     *
     * @param  \App\Models\Customer  $customer
     */
    public function syncCustomer($customer): void
    {
        $max = Customer::max('fortnox_id') ?? 9999;
        $customerNumber = $max + 1;

        while (true) {
            try {
                $response = $this->createCustomer(CreateFortnoxCustomerRequestDTO::from([
                    'customer_number' => $customerNumber,
                    'name' => $customer->name,
                    'organisation_number' => $customer->identity_number,
                    'address1' => $customer->address ? $customer->address->address : null,
                    'address2' => $customer->address ? $customer->address->address_2 : null,
                    'city' => $customer->address ? $customer->address->city->name : null,
                    'currency' => $customer->users->first()->info->currency,
                    'country_code' => $customer->address ? $customer->address->city->country->code : null,
                    'email' => $customer->email,
                    'phone1' => $customer->phone1,
                    'type' => Str::upper($customer->membership_type),
                    'zip_code' => $customer->address ? $customer->address->postal_code : null,
                    'default_delivery_types' => [
                        'invoice' => strtoupper($customer->invoice_method),
                    ],
                    'email_invoice' => $customer->email,
                ]));

                if ($response) {
                    $customer->update([
                        'fortnox_id' => $response->customer_number,
                    ]);

                    // Update all customers that reference this customer
                    Customer::where('customer_ref_id', $customer->id)
                        ->update(['fortnox_id' => $response->customer_number]);
                }

                break;
            } catch (OperationFailedException $e) {
                if (str_contains($e->getMessage(), 'används redan')) {
                    // If customer number is already in use, try next number
                    $customerNumber++;
                } else {
                    throw $e;
                }
            }
        }
    }
}
