<?php

namespace App\DTOs\Fortnox\Article;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class UpdateArticleRequestDTO extends BaseData
{
    public function __construct(
        public string $description,
        public string|Optional $disposable_quantity,
        public string|Optional $ean,
        public bool $housework,
        public string $housework_type,
        public string|Optional $purchase_price,
        public string|Optional $sales_price,
        public string|Optional $note,
        public float|Optional $quantity_in_stock,
        public string|Optional $reserved_quantity,
        public string|Optional $stock_place,
        public string|Optional $stock_value,
        public string $type,
        public string $unit,
        public string $VAT,
        public bool|Optional $webshop_article,
        public string|Optional $sales_account,
    ) {
    }
}
