<?php

namespace App\DTOs\ServiceQuarter;

use App\DTOs\BaseData;
use App\DTOs\Service\ServiceResponseDTO;
use App\Models\ServiceQuarter;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ServiceQuarterResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $serviceId,
        public Lazy|null|int $minSquareMeters,
        public Lazy|null|int $maxSquareMeters,
        public Lazy|null|int $quarters,
        public Lazy|null|float $hours,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|ServiceResponseDTO $service,
    ) {
    }

    public static function fromModel(ServiceQuarter $serviceQuarter): self
    {
        return new self(
            Lazy::create(fn () => $serviceQuarter->id)->defaultIncluded(),
            Lazy::create(fn () => $serviceQuarter->service_id)->defaultIncluded(),
            Lazy::create(fn () => $serviceQuarter->min_square_meters)->defaultIncluded(),
            Lazy::create(fn () => $serviceQuarter->max_square_meters)->defaultIncluded(),
            Lazy::create(fn () => $serviceQuarter->quarters)->defaultIncluded(),
            Lazy::create(fn () => $serviceQuarter->hours)->defaultIncluded(),
            Lazy::create(fn () => $serviceQuarter->created_at)->defaultIncluded(),
            Lazy::create(fn () => $serviceQuarter->updated_at)->defaultIncluded(),
            Lazy::create(fn () => ServiceResponseDTO::from($serviceQuarter->service)),
        );
    }
}
