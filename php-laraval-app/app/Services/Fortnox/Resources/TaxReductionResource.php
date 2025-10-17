<?php

namespace App\Services\Fortnox\Resources;

use App\DTOs\Fortnox\TaxReduction\CreateTaxReductionRequestDTO;
use App\DTOs\Fortnox\TaxReduction\TaxReductionDTO;
use App\DTOs\Fortnox\TaxReduction\UpdateTaxReductionRequestDTO;
use App\Exceptions\OperationFailedException;
use Illuminate\Http\Response;

trait TaxReductionResource
{
    /**
     * Get list of articles.
     *
     * @return \Spatie\LaravelData\DataCollection<array-key,\App\DTOs\Fortnox\TaxReduction\TaxReductionDTO>
     */
    public function getTaxReductions(
        string $filter = null,
        int $referenceNumber = null,
    ) {
        $query = [
            'filter' => $filter,
            'referencenumber' => $referenceNumber,
        ];
        $response = $this->sendRequest('taxreductions', 'GET', query: $query);

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
                        'failed to get tax reduction',
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

        return TaxReductionDTO::collection($response->json('TaxReductions'));
    }

    public function createTaxReduction(CreateTaxReductionRequestDTO $data): TaxReductionDTO
    {
        $response = $this->sendRequest(
            'taxreductions',
            'POST',
            body: ['TaxReduction' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create tax reduction',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'asked_amount' => $data->asked_amount,
                                'customer_name' => $data->customer_name,
                                'reference_number' => $data->reference_number,
                                'social_security_number' => $data->social_security_number,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return TaxReductionDTO::from($response->json('TaxReduction'));
    }

    /**
     * Update tax reduction on Fortnox.
     */
    public function updateTaxReduction(string $taxReductionId, UpdateTaxReductionRequestDTO $data): TaxReductionDTO
    {
        $response = $this->sendRequest(
            "taxreductions/{$taxReductionId}",
            'PUT',
            body: ['TaxReduction' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update tax reduction',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'tax_reduction_id' => $taxReductionId,
                                'asked_amount' => $data->asked_amount,
                                'customer_name' => $data->customer_name,
                                'reference_number' => $data->reference_number,
                                'social_security_number' => $data->social_security_number,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return TaxReductionDTO::from($response->json('TaxReduction'));
    }

    /**
     * Delete tax reduction on Fortnox.
     */
    public function deleteTaxReduction(string $taxReductionId): void
    {
        $response = $this->sendRequest("taxreductions/{$taxReductionId}", 'DELETE');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_NO_CONTENT) {
            throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
        }
    }
}
