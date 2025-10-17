<?php

namespace App\Services\Fortnox\Resources;

use App\DTOs\Fortnox\AbsenceTransaction\AbsenceTransactionDTO;
use App\DTOs\Fortnox\AbsenceTransaction\CreateAbsenceTransactionRequestDTO;
use App\DTOs\Fortnox\AbsenceTransaction\UpdateAbsenceTransactionRequestDTO;
use App\Exceptions\OperationFailedException;
use Illuminate\Http\Response;

trait AbsenceTransactionResource
{
    /**
     * Get list of articles.
     *
     *
     * @param  string|null  $date
     * @param  int|null  $employeeId
     * @return \Spatie\LaravelData\DataCollection<array-key,\App\DTOs\Fortnox\Article\ArticleDTO>
     */
    public function getAbsenceTransactions($date = null, $employeeId = null)
    {
        $response = $this->sendRequest('absencetransactions', 'GET', query: [
            'date' => $date,
            'employeeid' => $employeeId,
        ]);

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __('failed to get absence transaction', [
                        'reason' => $errorInfo['message'],
                        'details' => json_encode(['date' => $date, 'employeeId' => $employeeId]),
                    ]),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return AbsenceTransactionDTO::collection($response->json('AbsenceTransactions'));
    }

    public function createAbsenceTransaction(CreateAbsenceTransactionRequestDTO $data): AbsenceTransactionDTO
    {
        $response = $this->sendRequest(
            'absencetransactions',
            'POST',
            body: ['AbsenceTransaction' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create absence transaction',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode($data->toArray()),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return AbsenceTransactionDTO::from($response->json('AbsenceTransaction'));
    }

    /**
     * Update absence transaction on Fortnox.
     */
    public function updateAbsenceTransaction(
        string $absencetransactionId,
        UpdateAbsenceTransactionRequestDTO $data
    ): AbsenceTransactionDTO {
        $response = $this->sendRequest(
            "absencetransactions/{$absencetransactionId}",
            'PUT',
            body: ['AbsenceTransaction' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update absence transaction',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode($data->toArray()),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return AbsenceTransactionDTO::from($response->json('AbsenceTransaction'));
    }
}
