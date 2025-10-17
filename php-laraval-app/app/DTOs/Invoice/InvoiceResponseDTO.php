<?php

namespace App\DTOs\Invoice;

use App\DTOs\BaseData;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\Order\OrderResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\Invoice;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class InvoiceResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $customerId,
        public Lazy|null|int $fortnoxInvoiceId,
        public Lazy|null|int $fortnoxTaxReductionId,
        public Lazy|null|string $type,
        public Lazy|null|string $category,
        public Lazy|null|int $month,
        public Lazy|null|int $year,
        public Lazy|null|string $remark,
        public Lazy|null|float $totalGross,
        public Lazy|null|float $totalNet,
        public Lazy|null|float $totalVat,
        public Lazy|null|float $totalRut,
        public Lazy|null|float $totalIncludeVat,
        public Lazy|null|float $totalInvoiced,
        public Lazy|null|string $status,
        public Lazy|null|string $sentAt,
        public Lazy|null|string $dueAt,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|CustomerResponseDTO $customer,
        public Lazy|null|UserResponseDTO $user,
        #[DataCollectionOf(OrderResponseDTO::class)]
        public Lazy|null|DataCollection $orders,
    ) {
    }

    public static function fromModel(Invoice $invoice): self
    {
        return new self(
            Lazy::create(fn () => $invoice->id)->defaultIncluded(),
            Lazy::create(fn () => $invoice->customer_id)->defaultIncluded(),
            Lazy::create(fn () => $invoice->fortnox_invoice_id)->defaultIncluded(),
            Lazy::create(fn () => $invoice->fortnox_tax_reduction_id)->defaultIncluded(),
            Lazy::create(fn () => $invoice->type)->defaultIncluded(),
            Lazy::create(fn () => $invoice->category)->defaultIncluded(),
            Lazy::create(fn () => $invoice->month)->defaultIncluded(),
            Lazy::create(fn () => $invoice->year)->defaultIncluded(),
            Lazy::create(fn () => $invoice->remark)->defaultIncluded(),
            Lazy::create(fn () => $invoice->total_gross)->defaultIncluded(),
            Lazy::create(fn () => $invoice->total_net)->defaultIncluded(),
            Lazy::create(fn () => $invoice->total_vat)->defaultIncluded(),
            Lazy::create(fn () => $invoice->total_rut)->defaultIncluded(),
            Lazy::create(fn () => $invoice->total_include_vat)->defaultIncluded(),
            Lazy::create(fn () => $invoice->total_invoiced)->defaultIncluded(),
            Lazy::create(fn () => $invoice->status)->defaultIncluded(),
            Lazy::create(fn () => $invoice->sent_at->shiftTimezone('Europe/Stockholm'))
                ->defaultIncluded(),
            Lazy::create(fn () => $invoice->due_at->shiftTimezone('Europe/Stockholm'))
                ->defaultIncluded(),
            Lazy::create(fn () => $invoice->created_at)->defaultIncluded(),
            Lazy::create(fn () => $invoice->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $invoice->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => CustomerResponseDTO::from($invoice->customer)),
            Lazy::create(fn () => UserResponseDTO::from($invoice->user)),
            Lazy::create(fn () => OrderResponseDTO::collection(
                $invoice->orders
            )),
        );
    }
}
