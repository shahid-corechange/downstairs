<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\Models\UserInfo;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UserInfoResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|string $avatar,
        public Lazy|null|string $language,
        public Lazy|null|string $timezone,
        public Lazy|null|string $currency,
        public Lazy|null|string $notificationMethod,
        public Lazy|null|string $twoFactorAuth,
        public Lazy|null|int $marketing,
    ) {
    }

    public static function fromModel(UserInfo $info): self
    {
        return new self(
            Lazy::create(fn () => $info->avatar)->defaultIncluded(),
            Lazy::create(fn () => $info->language)->defaultIncluded(),
            Lazy::create(fn () => $info->timezone)->defaultIncluded(),
            Lazy::create(fn () => $info->currency)->defaultIncluded(),
            Lazy::create(fn () => $info->notification_method)->defaultIncluded(),
            Lazy::create(fn () => $info->two_factor_auth)->defaultIncluded(),
            Lazy::create(fn () => $info->marketing)->defaultIncluded(),
        );
    }
}
