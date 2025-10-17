<?php

namespace App\Services\Fortnox\Resources;

use App\DTOs\Fortnox\InvoicePayment\CreateInvoicePaymentRequestDTO;
use App\DTOs\Fortnox\InvoicePayment\InvoicePaymentDTO;
use App\DTOs\Fortnox\InvoicePayment\UpdateInvoicePaymentRequestDTO;
use App\Exceptions\OperationFailedException;
use Illuminate\Http\Response;

trait InvoicePaymentResource
{
    /**
     * Get list of invoice payments.
     *
     * @return \Spatie\LaravelData\DataCollection<array-key,\App\DTOs\Fortnox\Invoice\InvoiceDTO>
     */
    public function getInvoicePayments(
        string $invoiceNumber = null,
        string $lastModified = null,
        string $sortBy = null,
    ) {
        $query = [
            'invoicenumber' => $invoiceNumber,
            'lastmodified' => $lastModified,
            'sortby' => $sortBy,
        ];
        $response = $this->sendRequest('invoicepayments', 'GET', query: $query);

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
                        'failed to get invoice payments',
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

        return InvoicePaymentDTO::collection($response->json('InvoicePayments'));
    }

    /**
     * Get an invoice payment.
     */
    public function getInvoicePayment(string $documentNumber): InvoicePaymentDTO
    {
        $response = $this->sendRequest("invoices/{$documentNumber}", 'GET');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to get invoice payment',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'document_number' => $documentNumber,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return InvoicePaymentDTO::from($response->json('InvoicePayment'));
    }

    public function createInvoicePayment(CreateInvoicePaymentRequestDTO $data): InvoicePaymentDTO
    {
        $response = $this->sendRequest(
            'invoicepayments',
            'POST',
            body: ['InvoicePayment' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create invoice payment',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'invoice_number' => $data->invoice_number,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return InvoicePaymentDTO::from($response->json('InvoicePayment'));
    }

    public function updateInvoicePayment(
        string $invoicePaymentId,
        UpdateInvoicePaymentRequestDTO $data
    ): InvoicePaymentDTO {
        $response = $this->sendRequest(
            "invoicepayments/{$invoicePaymentId}",
            'PUT',
            body: ['InvoicePayment' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update invoice payment',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'invoice_payment_id' => $invoicePaymentId,
                                'invoice_number' => $data->invoice_number,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return InvoicePaymentDTO::from($response->json('InvoicePayment'));
    }

    public function bookkeepInvoicePayment(
        string $invoicePaymentId,
        UpdateInvoicePaymentRequestDTO $data
    ): InvoicePaymentDTO {
        $response = $this->sendRequest(
            "invoicepayments/{$invoicePaymentId}/bookkeep",
            'PUT',
            body: ['InvoicePayment' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to bookkeep invoice payment',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'invoice_payment_id' => $invoicePaymentId,
                                'invoice_number' => $data->invoice_number,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return InvoicePaymentDTO::from($response->json('InvoicePayment'));
    }
}
