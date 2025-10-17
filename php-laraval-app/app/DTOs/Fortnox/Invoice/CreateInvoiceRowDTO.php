<?php

namespace App\DTOs\Fortnox\Invoice;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class CreateInvoiceRowDTO extends BaseData
{
    public function __construct(
        public int|Optional $account_number,
        public string|Optional|null $article_number,
        public string|Optional $cost_center,
        public string|Optional $delivered_quantity,
        public string|Optional $description,
        public float|Optional $discount,
        public string|Optional $discount_type,
        public bool|Optional $house_work,
        public int|Optional $house_work_hours_to_report,
        public string|Optional $house_work_type,
        public float|Optional $price,
        public string|Optional $project,
        public int|Optional $row_id,
        public string|Optional $stock_point_code,
        public string|Optional $unit,
        public int|Optional $VAT,
    ) {
    }
}
