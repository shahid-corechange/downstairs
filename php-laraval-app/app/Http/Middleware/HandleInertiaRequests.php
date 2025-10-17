<?php

namespace App\Http\Middleware;

use App\DTOs\Store\StoreResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Http\Traits\QueryStringTrait;
use Auth;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    use QueryStringTrait;

    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Set the root view that is loaded on the first page visit.
     */
    public function setRootView(string $rootView): self
    {
        $this->rootView = $rootView;

        return $this;
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var \App\Models\User|null */
        $user = Auth::user();

        return array_merge(parent::share($request), array_merge(
            array_keys_to_camel_case($this->getAllQueries()),
            [
                'sessionId' => Auth::id(),
                'storeId' => $request->session()->get('store_id'),
                'user' => ! $user ? null : UserResponseDTO::from($user)
                    ->include('info', 'permissions', 'roles')
                    ->only(
                        'id',
                        'firstName',
                        'lastName',
                        'fullname',
                        'email',
                        'permissions',
                        'info.{avatar,language,timezone,currency,twoFactorAuth}',
                        'roles.name',
                    ),
                'stores' => ! $user ? [] : StoreResponseDTO::collection($user->stores)
                    ->only('id', 'name'),
                'flash' => [
                    'success' => fn () => $request->session()->get('success'),
                    'successPayload' => fn () => $request->session()->get('successPayload'),
                    'error' => fn () => $request->session()->get('error'),
                    'errorPayload' => fn () => $request->session()->get('errorPayload'),
                ],
            ]
        ));
    }
}
