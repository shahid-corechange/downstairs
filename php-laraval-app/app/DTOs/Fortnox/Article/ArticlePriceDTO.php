<?php

namespace App\DTOs\Fortnox\Article;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class ArticlePriceDTO extends BaseData
{
    public function __construct(
        public string $article_number,
        public int $from_quantity,
        public int $percent,
        public int $price,
        public string $price_list,
        public ?string $date,
    ) {
    }
}
