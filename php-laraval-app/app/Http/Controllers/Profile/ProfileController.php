<?php

namespace App\Http\Controllers\Profile;

use App\Contracts\StorageService;
use App\DTOs\User\UpdateProfileRequestDTO;
use App\Enums\Azure\BlobStorage\BlobStorageContainerEnum;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use App\Http\Controllers\Controller;
use Auth;
use Cache;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Profile/index');
    }

    public function update(UpdateProfileRequestDTO $request, StorageService $storage): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->toArray();
        $userData = array_intersect_key($data, array_flip(['first_name', 'last_name']));
        $infoData = array_diff_key($data, array_flip(['first_name', 'last_name']));

        if (! $request->isOptional('avatar')) {
            $filename = generate_filename('user', $request->avatar->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'avatar',
                $filename
            );
            $infoData['avatar'] = $url;
        }

        if ($user) {
            DB::transaction(function () use ($userData, $infoData, $user) {
                $user->update($userData);
                $user->info->update($infoData);
            });

            Cache::set("user_{$user->id}", $user, 3600 * 8);
        }

        return back()->with('success', __('profile updated successfully'));
    }
}
