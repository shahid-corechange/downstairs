<?php

namespace App\DTOs\Fortnox\Article;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapOutputName(StudlyCaseMapper::class)]
class UpdateArticlePriceRequestDTO extends BaseData
{
    public function __construct(
        public string $article_number,
        public ?int $from_quantity,
        public ?float $price,
        public string $price_list,
    ) {
    }
}
