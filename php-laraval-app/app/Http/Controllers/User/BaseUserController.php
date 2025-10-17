<?php

namespace App\Http\Controllers\User;

use App\Enums\User\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\UserSettingTrait;
use App\Models\User;
use Hash;

class BaseUserController extends Controller
{
    use UserSettingTrait;

    protected function createUser(array $data, array $roles): User
    {
        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'status' => UserStatusEnum::Active(),
        ]);
        $user->assignRole($roles);
        $this->createDefaultSettings($user);

        return $user;
    }
}
