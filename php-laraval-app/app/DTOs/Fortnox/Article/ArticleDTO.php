<?php

namespace App\DTOs\Fortnox\Article;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class ArticleDTO extends BaseData
{
    public function __construct(
        public ?string $article_number,
        public ?string $description,
        public ?string $disposable_quantity,
        public ?string $ean,
        public ?bool $housework,
        public ?string $purchase_price,
        public ?string $sales_price,
        public ?float $quantity_in_stock,
        public ?string $reserved_quantity,
        public ?string $stock_place,
        public ?string $stock_value,
        public ?string $unit,
        public ?string $vat,
        public ?bool $webshop_article,
    ) {
    }
}
