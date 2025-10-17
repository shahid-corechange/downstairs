<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;

trait AvatarTrait
{
    private function storeAvatar($avatar): ?string
    {
        if (request()->hasFile('avatar')) {
            return request()->file('avatar')->storePublicly('images', 'public');
        }

        return $avatar;
    }

    private function deleteAvatar($avatar)
    {
        $public = 'public/';

        if (request()->boolean('avatar_remove')) {
            if (Storage::exists($public.$avatar)) {
                Storage::delete($public.$avatar);
            }

            return null;
        }
    }
}
