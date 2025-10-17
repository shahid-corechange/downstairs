<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class BulkChangeWorkerRequestDTO extends BaseData
{
    public function __construct(
        #[DataCollectionOf(ChangeWorkerRequestDTO::class)]
        public DataCollection $changes
    ) {
    }

    public static function rules(): array
    {
        return [
            'changes' => 'required|array|min:1',
        ];
    }
}
