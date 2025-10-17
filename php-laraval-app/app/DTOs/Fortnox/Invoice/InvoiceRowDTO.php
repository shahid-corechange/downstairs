<?php

namespace App\DTOs\Fortnox\Invoice;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class InvoiceRowDTO extends BaseData
{
    public function __construct(
        public ?int $account_number,
        public ?string $article_number,
        public ?string $contribution_percent,
        public ?string $contribution_value,
        public ?string $cost_center,
        public ?string $delivered_quantity,
        public ?string $description,
        public ?float $discount,
        public ?string $discount_type,
        public ?bool $house_work,
        public ?int $house_work_hours_to_report,
        public ?string $house_work_type,
        public ?float $price,
        public ?float $price_excluding_vat,
        public ?string $project,
        public ?int $row_id,
        public ?string $stock_point_code,
        public ?float $total,
        public ?float $total_excluding_vat,
        public ?string $unit,
        public ?int $vat,
    ) {
    }
}
