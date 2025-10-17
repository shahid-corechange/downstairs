<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\DTOs\Credit\CreditResponseDTO;
use App\DTOs\Credit\CreditTransactionResponseDTO;
use App\Models\Credit;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Services\CreditService;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UserCreditResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $total,
        public Lazy|null|CreditResponseDTO $expiringCredit,
        #[DataCollectionOf(CreditResponseDTO::class)]
        public Lazy|null|DataCollection $credits,
        #[DataCollectionOf(CreditTransactionResponseDTO::class)]
        public Lazy|null|DataCollection $transactions,
    ) {
    }

    public static function fromModel(User $user): self
    {
        $service = new CreditService();
        $service->load($user->id);

        return new self(
            Lazy::create(fn () => $service->getTotal())->defaultIncluded(),
            Lazy::create(fn () => $service->getExpiring() ?
                CreditResponseDTO::from($service->getExpiring()) :
                null)->defaultIncluded(),
            Lazy::create(fn () => CreditResponseDTO::collection(
                Credit::valid()->where('user_id', $user->id)->get()
            )),
            Lazy::create(fn () => CreditTransactionResponseDTO::collection(
                CreditTransaction::where('user_id', $user->id)->get()
            )),
        );
    }
}
