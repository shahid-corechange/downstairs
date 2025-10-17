<?php

namespace App\Http\Controllers\Invoice;

use App\DTOs\Invoice\InvoiceResponseDTO;
use App\DTOs\Invoice\UpdateInvoiceRequestDTO;
use App\DTOs\Invoice\UpdateInvoiceRowRequestDTO;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Invoice;
use App\Models\OrderFixedPriceRow;
use App\Models\OrderRow;
use App\Services\InvoiceSummationService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user',
        'customer',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'fortnoxInvoiceId',
        'type',
        'category',
        'month',
        'year',
        'totalGross',
        'totalNet',
        'totalVat',
        'totalIncludeVat',
        'totalRut',
        'totalInvoiced',
        'sentAt',
        'dueAt',
        'status',
        'user.id',
        'user.fullname',
        'customer.membershipType',
        'customer.dueDays',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            sort: ['created_at' => 'desc'],
            pagination: 'page',
            show: 'all'
        );
        $paginatedData = Invoice::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Invoice/Overview/index', [
            'invoices' => InvoiceResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'transportArticleId' => get_transport()->fortnox_article_id,
            'materialArticleId' => get_material()->fortnox_article_id,
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Invoice::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            InvoiceResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Display the specified resource as json.
     */
    public function jsonShow(int $invoiceId): JsonResponse
    {
        $data = Invoice::selectWithRelations(mergeFields: true)
            ->findOrFail($invoiceId);

        return $this->successResponse(
            InvoiceResponseDTO::transformData($data),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateInvoiceRequestDTO $request,
        Invoice $invoice,
        InvoiceSummationService $invoiceSummationService
    ): RedirectResponse {
        if ($invoice->status !== InvoiceStatusEnum::Open()) {
            return back()->with('error', __('invoice status not open'));
        }

        $shouldSkipRows = $request->isOptional('rows') || is_null($request->rows);
        $typeGroupedRows = [];
        $typeGroupedNewRows = [];

        if (! $shouldSkipRows) {
            /** @var UpdateInvoiceRowRequestDTO $row */
            foreach ($request->rows as $row) {
                // Skip if all fields are empty. This will prevent error in the Fortnox.
                if (empty($row->description) && empty($row->unit) && empty($row->quantity) && empty($row->price)) {
                    continue;
                }

                if (! isset($typeGroupedRows[$row->type])) {
                    $typeGroupedRows[$row->type] = [];
                }

                if (! isset($typeGroupedRows[$row->type][$row->parent_id])) {
                    $typeGroupedRows[$row->type][$row->parent_id] = collect();
                }

                $typeGroupedRows[$row->type][$row->parent_id]->push($row);

                if (! $row->id) {
                    if (! isset($typeGroupedNewRows[$row->type])) {
                        $typeGroupedNewRows[$row->type] = [];
                    }

                    if ($row->type === 'order') {
                        $typeGroupedNewRows[$row->type][] = [
                            'order_id' => $row->parent_id,
                            'description' => $row->description,
                            'quantity' => $row->quantity,
                            'unit' => $row->unit,
                            'price' => $row->price,
                            'discount_percentage' => $row->discount_percentage,
                            'vat' => $row->vat,
                            'has_rut' => $row->has_rut,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } elseif ($row->type === 'fixed price') {
                        $typeGroupedNewRows[$row->type][] = [
                            'order_fixed_price_id' => $row->parent_id,
                            'type' => $row->description,
                            'description' => $row->description,
                            'quantity' => $row->quantity,
                            'price' => $row->price,
                            'vat_group' => $row->vat,
                            'has_rut' => $row->has_rut,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            if (empty($typeGroupedRows)) {
                return back()->with('error', __('invoice rows cannot be empty'));
            }
        }

        DB::transaction(function () use (
            $invoice,
            $typeGroupedRows,
            $typeGroupedNewRows,
            $request,
            $invoiceSummationService,
        ) {
            /**
             * @var string $type
             * @var array<int,\Illuminate\Support\Collection<array-key,UpdateInvoiceRowRequestDTO>> $groupedRows
             **/
            foreach ($typeGroupedRows as $type => $groupedRows) {
                /** @var int[] */
                $parentIds = array_keys($groupedRows);

                if ($type === 'order') {
                    /** @var \Illuminate\Support\Collection<array-key,OrderRow> */
                    $orderRows = OrderRow::whereIn('order_id', $parentIds)->get();

                    foreach ($orderRows as $orderRow) {
                        $updatedRow = $groupedRows[$orderRow->order_id]->firstWhere('id', $orderRow->id);

                        if (! $updatedRow) {
                            // Delete the row if it is not in the request
                            $orderRow->delete();
                        } elseif ($this->isOrderRowUpdated($orderRow, $updatedRow)) {
                            // Update the row if any of the field is different
                            $orderRow->update([
                                'description' => trim(placeholder($updatedRow->description)),
                                'quantity' => $updatedRow->quantity,
                                'unit' => placeholder($updatedRow->unit),
                                'price' => $updatedRow->price,
                                'discount_percentage' => $updatedRow->discount_percentage,
                                'vat' => $updatedRow->vat,
                                'has_rut' => $updatedRow->has_rut,
                            ]);
                        }
                    }

                    if (isset($typeGroupedNewRows[$type]) && ! empty($typeGroupedNewRows[$type])) {
                        // Insert the new rows
                        OrderRow::insert($typeGroupedNewRows[$type]);
                    }
                } elseif ($type === 'fixed price') {
                    /** @var \Illuminate\Support\Collection<array-key,OrderFixedPriceRow> */
                    $orderFixedPriceRows = OrderFixedPriceRow::whereIn('order_fixed_price_id', $parentIds)->get();

                    foreach ($orderFixedPriceRows as $orderFixedPriceRow) {
                        $updatedRow = $groupedRows[$orderFixedPriceRow->order_fixed_price_id]
                            ->firstWhere('id', $orderFixedPriceRow->id);

                        if (! $updatedRow) {
                            // Delete the row if it is not in the request
                            $orderFixedPriceRow->delete();
                        } elseif ($this->isOrderFixedPriceRowUpdated($orderFixedPriceRow, $updatedRow)) {
                            // Update the row if any of the field is different
                            $orderFixedPriceRow->update([
                                'description' => trim(placeholder($updatedRow->description)),
                                'quantity' => $updatedRow->quantity,
                                'price' => $updatedRow->price,
                                'vat_group' => $updatedRow->vat,
                                'has_rut' => $updatedRow->has_rut,
                            ]);
                        }
                    }

                    if (isset($typeGroupedNewRows[$type]) && ! empty($typeGroupedNewRows[$type])) {
                        // Insert the new rows
                        OrderFixedPriceRow::insert($typeGroupedNewRows[$type]);
                    }
                }
            }

            $remark = $request->isNotOptional('remark') ? trim(placeholder($request->remark)) : $invoice->remark;
            $sentAt = $request->isNotOptional('sent_at') ? $request->sent_at : $invoice->sent_at;
            $dueAt = $request->isNotOptional('sent_at') ?
                Carbon::parse($request->sent_at)->addDays($invoice->customer->due_days) : $invoice->due_at;
            $summation = $invoiceSummationService->getSummation($invoice);

            $invoice->update([
                'remark' => $remark,
                'sent_at' => $sentAt,
                'due_at' => $dueAt,
                ...$summation,
            ]);
        });

        return back()->with('success', __('invoice updated successfully'));
    }

    /**
     * Check if the order row is updated.
     */
    private function isOrderRowUpdated(OrderRow $orderRow, UpdateInvoiceRowRequestDTO $updatedRow): bool
    {
        return trim(placeholder($orderRow->description)) !== trim(placeholder($updatedRow->description)) ||
            $orderRow->quantity !== $updatedRow->quantity ||
            placeholder($orderRow->unit) !== placeholder($updatedRow->unit) ||
            $orderRow->price !== $updatedRow->price ||
            $orderRow->discount_percentage !== $updatedRow->discount_percentage ||
            $orderRow->vat !== $updatedRow->vat;
    }

    /**
     * Check if the order fixed price row is updated.
     */
    private function isOrderFixedPriceRowUpdated(
        OrderFixedPriceRow $orderFixedPriceRow,
        UpdateInvoiceRowRequestDTO $updatedRow
    ): bool {
        return trim(placeholder($orderFixedPriceRow->description)) !== trim(placeholder($updatedRow->description)) ||
            $orderFixedPriceRow->quantity !== $updatedRow->quantity ||
            $orderFixedPriceRow->price !== $updatedRow->price ||
            $orderFixedPriceRow->vat_group !== $updatedRow->vat ||
            $orderFixedPriceRow->has_rut !== $updatedRow->has_rut;
    }
}
