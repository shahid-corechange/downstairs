<?php

namespace App\DTOs\BlockDay;

use App\DTOs\BaseData;
use App\Models\BlockDay;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class BlockDayResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $blockDate,
        public Lazy|null|string $startBlockTime,
        public Lazy|null|string $endBlockTime,
    ) {
    }

    public static function fromModel(BlockDay $blockDay): self
    {
        return new self(
            Lazy::create(fn () => $blockDay->id)->defaultIncluded(),
            Lazy::create(fn () => $blockDay->block_date)->defaultIncluded(),
            Lazy::create(fn () => $blockDay->start_block_time)->defaultIncluded(),
            Lazy::create(fn () => $blockDay->end_block_time)->defaultIncluded(),
        );
    }
}
