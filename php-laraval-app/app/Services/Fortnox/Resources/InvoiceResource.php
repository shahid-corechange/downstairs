<?php

namespace App\Services\Fortnox\Resources;

use App\DTOs\Fortnox\Invoice\CreateInvoiceRequestDTO;
use App\DTOs\Fortnox\Invoice\InvoiceDTO;
use App\DTOs\Fortnox\Invoice\UpdateInvoiceRequestDTO;
use App\Exceptions\OperationFailedException;
use Illuminate\Http\Response;

trait InvoiceResource
{
    /**
     * Get list of invoices.
     *
     * @return \Spatie\LaravelData\DataCollection<array-key,\App\DTOs\Fortnox\Invoice\InvoiceDTO>
     */
    public function getInvoices(
        string $filter = null,
        string $costCenter = null,
        string $customerName = null,
        string $customerNumber = null,
        string $label = null,
        string $documentNumber = null,
        string $fromDate = null,
        string $toDate = null,
        string $fromFinalPayDate = null,
        string $toFinalPayDate = null,
        string $lastModified = null,
        string $notCompleted = null,
        string $ocr = null,
        string $ourReference = null,
        string $project = null,
        string $sent = null,
        string $externalInvoiceReference1 = null,
        string $externalInvoiceReference2 = null,
        string $yourReference = null,
        string $invoiceType = null,
        string $articleNumber = null,
        string $articleDescription = null,
        string $currency = null,
        string $accountNumberFrom = null,
        string $accountNumberTo = null,
        string $yourOrderNumber = null,
        string $credit = null,
        string $sortBy = null,
    ) {
        $query = [
            'filter' => $filter,
            'costcenter' => $costCenter,
            'customername' => $customerName,
            'customernumber' => $customerNumber,
            'label' => $label,
            'documentnumber' => $documentNumber,
            'fromdate' => $fromDate,
            'todate' => $toDate,
            'fromfinalpaydate' => $fromFinalPayDate,
            'tofinalpaydate' => $toFinalPayDate,
            'lastmodified' => $lastModified,
            'notcompleted' => $notCompleted,
            'ocr' => $ocr,
            'ourreference' => $ourReference,
            'project' => $project,
            'sent' => $sent,
            'externalinvoicereference1' => $externalInvoiceReference1,
            'externalinvoicereference2' => $externalInvoiceReference2,
            'yourreference' => $yourReference,
            'invoicetype' => $invoiceType,
            'articlenumber' => $articleNumber,
            'articledescription' => $articleDescription,
            'currency' => $currency,
            'accountnumberfrom' => $accountNumberFrom,
            'accountnumberto' => $accountNumberTo,
            'yourordernumber' => $yourOrderNumber,
            'credit' => $credit,
            'sortby' => $sortBy,
        ];
        $response = $this->sendRequest('invoices', 'GET', query: $query);

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
                        'failed to get invoices',
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

        return InvoiceDTO::collection($response->json('Invoices'));
    }

    /**
     * Get an invoice.
     */
    public function getInvoice(string $documentNumber): InvoiceDTO
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
                        'failed to get invoice',
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

        return InvoiceDTO::from($response->json('Invoice'));
    }

    /**
     * Bookkeep an invoice.
     */
    public function bookkeepInvoice(string $documentNumber)
    {
        $response = $this->sendRequest("invoices/{$documentNumber}/bookkeep", 'PUT');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to bookkeep invoice',
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

        return $response;
    }

    /**
     * Cancel an invoice.
     */
    public function cancelInvoice(string $documentNumber)
    {
        $response = $this->sendRequest("invoices/{$documentNumber}/cancel", 'PUT');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to cancel invoice',
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

        return $response;
    }

    /**
     * Credit an invoice.
     */
    public function creditInvoice(string $documentNumber)
    {
        $response = $this->sendRequest("invoices/{$documentNumber}/credit", 'PUT');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to credit invoice',
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

        return $response;
    }

    /**
     * Set an invoice as sent.
     */
    public function setInvoiceAsSent(string $documentNumber)
    {
        $response = $this->sendRequest("invoices/{$documentNumber}/externalprint", 'PUT');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to mark invoice as sent',
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

        return $response;
    }

    /**
     * Set an invoice as done.
     */
    public function setInvoiceAsDone(string $documentNumber)
    {
        $response = $this->sendRequest("invoices/{$documentNumber}/warehouseready", 'PUT');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to mark invoice as done',
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

        return $response;
    }

    public function createInvoice(CreateInvoiceRequestDTO $data): InvoiceDTO
    {
        $response = $this->sendRequest(
            'invoices',
            'POST',
            body: ['Invoice' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to create invoice',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'customer_name' => $data->customer_name,
                                'customer_number' => $data->customer_number,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return InvoiceDTO::from($response->json('Invoice'));
    }

    /**
     * Send invoice as email.
     */
    public function sendInvoiceAsEmail(string $documentNumber)
    {
        $response = $this->sendRequest("invoices/{$documentNumber}/email", 'GET');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to send invoice as email',
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

        return $response;
    }

    /**
     * Send invoice as e-print.
     */
    public function sendInvoiceAsEprint(string $documentNumber)
    {
        $response = $this->sendRequest("invoices/{$documentNumber}/eprint", 'GET');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to send invoice as eprint',
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

        return $response;
    }

    /**
     * Send invoice as e-invoice.
     */
    public function sendInvoiceAsEinvoice(string $documentNumber)
    {
        $response = $this->sendRequest("invoices/{$documentNumber}/einvoice", 'GET');

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($response->status() !== Response::HTTP_OK) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to send invoice as einvoice',
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

        return $response;
    }

    public function updateInvoice(string $invoiceId, UpdateInvoiceRequestDTO $data): InvoiceDTO
    {
        $response = $this->sendRequest(
            "invoices/{$invoiceId}",
            'PUT',
            body: ['Invoice' => $data->toArray()]
        );

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new OperationFailedException(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! in_array($response->status(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            $errorInfo = $response->json('ErrorInformation');

            if ($errorInfo && array_key_exists('message', $errorInfo)) {
                throw new OperationFailedException(
                    __(
                        'failed to update invoice',
                        [
                            'reason' => $errorInfo['message'],
                            'details' => json_encode([
                                'invoice_id' => $invoiceId,
                                'customer_name' => $data->customer_name,
                                'customer_number' => $data->customer_number,
                            ]),
                        ]
                    ),
                    Response::HTTP_BAD_GATEWAY
                );
            } else {
                throw new OperationFailedException(__('bad gateway'), Response::HTTP_BAD_GATEWAY);
            }
        }

        return InvoiceDTO::from($response->json('Invoice'));
    }
}
