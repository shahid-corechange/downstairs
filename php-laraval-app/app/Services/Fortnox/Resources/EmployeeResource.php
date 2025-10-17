<?php

namespace App\Services\Fortnox\Resources;

use App\DTOs\Fortnox\Employee\CreateFortnoxEmployeeRequestDTO;
use App\DTOs\Fortnox\Employee\EmployeeDTO;
use App\DTOs\Fortnox\Employee\UpdateFortnoxEmployeeRequestDTO;
use App\Exceptions\OperationFailedException;
use App\Models\Employee;
use Illuminate\Http\Response;
use Str;

trait EmployeeResource
{
    /**
     * Get list of employees.
     *
     * @return \Spatie\LaravelData\DataCollection<array-key,\App\DTOs\Fortnox\Employee\EmployeeDTO>
     */
    public function getEmployees(array $query = [])
    {
        $response = $this->sendRequest('employees', 'GET', query: $query);

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to get employees',
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

        return EmployeeDTO::collection($response->json('Employees'));
    }

    /**
     * Create employee on Fortnox.
     */
    public function createEmployee(CreateFortnoxEmployeeRequestDTO $data): EmployeeDTO
    {
        $response = $this->sendRequest(
            'employees',
            'POST',
            body: ['Employee' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if (($errorInfo && array_key_exists('code', $errorInfo) && $errorInfo['code'] === 2004299) ||
                ($errorInfo && array_key_exists('message', $errorInfo) &&
                Str::contains($errorInfo['message'], 'Felaktigt personnummer'))) {
                $data->personal_identity_number = 'API_BLANK';

                return $this->createEmployee($data);
            } elseif ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create employee',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'personal_identity_number' => $data->personal_identity_number,
                                'first_name' => $data->first_name,
                                'last_name' => $data->last_name,
                                'address1' => $data->address1,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return EmployeeDTO::from($response->json('Employee'));
    }

    /**
     * Update employee on Fortnox.
     */
    public function updateEmployee(string $employeeId, UpdateFortnoxEmployeeRequestDTO $data): EmployeeDTO
    {
        $response = $this->sendRequest(
            "employees/{$employeeId}",
            'PUT',
            body: ['Employee' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if (($errorInfo && array_key_exists('code', $errorInfo) && $errorInfo['code'] === 2004299) ||
                ($errorInfo && array_key_exists('message', $errorInfo)
                && Str::contains($errorInfo['message'], 'Felaktigt personnummer'))) {
                $data->personal_identity_number = 'API_BLANK';

                return $this->updateEmployee($employeeId, $data);
            } elseif ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update employee',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'employee_id' => $employeeId,
                                'personal_identity_number' => $data->personal_identity_number,
                                'first_name' => $data->first_name,
                                'last_name' => $data->last_name,
                                'address1' => $data->address1,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return EmployeeDTO::from($response->json('Employee'));
    }

    /**
     * Sync existing employee without fortnox_id to Fortnox.
     *
     * @param  \App\Models\Employee  $employee
     */
    public function syncEmployee($employee): void
    {
        $response = $this->createEmployee(CreateFortnoxEmployeeRequestDTO::from([
            'personal_identity_number' => $employee->identity_number,
            'first_name' => $employee->user->first_name,
            'last_name' => $employee->user->last_name,
            'address1' => $employee->address->address,
            'address2' => $employee->address->address_2,
            'post_code' => $employee->address->postal_code,
            'city' => $employee->address->city->name,
            'country' => $employee->address->city->country->name,
            'phone1' => $employee->phone1,
            'email' => $employee->email,
            'personel_type' => 'TJM',
        ]));

        if ($response) {
            $employee->update([
                'fortnox_id' => $response->employee_id,
                'is_valid_identity' => $response->personal_identity_number ? true : false,
            ]);
        }
    }
}
