<?php

namespace App\DTOs\Deviation;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class DeviationMetaResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $actualQuarters,
        #[DataCollectionOf(DeviationMetaProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
    ) {
    }
}
