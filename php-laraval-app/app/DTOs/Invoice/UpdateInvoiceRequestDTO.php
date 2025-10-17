<?php

namespace App\DTOs\Invoice;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateInvoiceRequestDTO extends BaseData
{
    public function __construct(
        public string|null|Optional $sent_at,
        public string|null|Optional $remark,
        #[DataCollectionOf(UpdateInvoiceRowRequestDTO::class)]
        public DataCollection|Optional|null $rows,
    ) {
    }

    public static function rules(): array
    {
        return [
            'sent_at' => 'nullable|date|after:now',
            'remark' => 'nullable|string',
            'rows' => 'nullable|array',
        ];
    }
}
