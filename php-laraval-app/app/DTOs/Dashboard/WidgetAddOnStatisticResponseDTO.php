<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\BaseData;
use App\Models\Addon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class WidgetAddOnStatisticResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|string $itemableType,
        public Lazy|null|int $itemableId,
        public Lazy|null|int $credit,
        public Lazy|null|int $currency,
        public Lazy|null|int $total,
        public Lazy|null|AddonResponseDTO $addon,
    ) {
        $addon = Addon::find($itemableId);

        if ($addon) {
            $this->addon = AddonResponseDTO::from($addon);
        }
    }
}
