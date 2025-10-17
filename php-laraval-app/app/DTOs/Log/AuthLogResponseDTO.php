<?php

namespace App\DTOs\Log;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\AuthenticationLog;
use App\Models\User;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class AuthLogResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|string $ipAddress,
        public Lazy|null|string $userAgent,
        public Lazy|null|string $loginAt,
        public Lazy|null|bool $loginSuccessful,
        public Lazy|null|string $logoutAt,
        public Lazy|null|bool $clearedByUser,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|AuthLogLocationResponseDTO $location,
    ) {
    }

    public static function fromModel(AuthenticationLog $log): self
    {
        return new self(
            Lazy::create(fn () => $log->id)->defaultIncluded(),
            Lazy::create(fn () => $log->authenticatable_type === User::class ?
                $log->authenticatable_id : null)->defaultIncluded(),
            Lazy::create(fn () => $log->ip_address)->defaultIncluded(),
            Lazy::create(fn () => $log->user_agent)->defaultIncluded(),
            Lazy::create(fn () => $log->login_at)->defaultIncluded(),
            Lazy::create(fn () => $log->login_successful)->defaultIncluded(),
            Lazy::create(fn () => $log->logout_at)->defaultIncluded(),
            Lazy::create(fn () => $log->cleared_by_user)->defaultIncluded(),
            Lazy::create(fn () => $log->authenticatable_type === User::class ?
                UserResponseDTO::from($log->authenticatable) : null)->defaultIncluded(),
            Lazy::create(fn () => AuthLogLocationResponseDTO::from($log->location)),
        );
    }
}
