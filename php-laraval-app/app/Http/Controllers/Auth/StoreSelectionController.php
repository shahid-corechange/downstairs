<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\StoreChangeRequestDTO;
use App\DTOs\Auth\StoreSelectionRequestDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Store;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StoreSelectionController extends Controller
{
    use ResponseTrait;

    /**
     * Handle an incoming authentication request.
     */
    public function login(StoreSelectionRequestDTO $request): RedirectResponse
    {
        $key = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'cellphone';

        if (! Auth::validate([$key => $request->user, 'password' => $request->password])) {
            return back()->withErrors([
                'user' => __('the provided credentials do not match our records'),
            ]);
        }

        /** @var \App\Models\User|null */
        $user = User::where($key, $request->user)->first();

        if (! $user || ! $user->isActive() || ! $user->can(PermissionsEnum::AccessCashier())) {
            return back()->withErrors([
                'user' => __('the provided credentials do not match our records'),
            ]);
        }

        Auth::login($user, $request->remember);

        if ($request->isNotOptional('store_id') && isset($request->store_id)) {
            // Store the store ID in the session
            request()->session()->put('store_id', $request->store_id);
            request()->session()->regenerate();

            return redirect()->route('cashier.search.index');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function change(StoreChangeRequestDTO $request)
    {
        $user = Auth::user();

        if ($request->isOptional('store_id')) {
            request()->session()->put('store_id', null);
            request()->session()->regenerate();

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $storeId = $request->store_id;

        if (! $storeId || ! $user->can(PermissionsEnum::AccessCashier()) || ! $user->stores->contains($storeId)) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        request()->session()->put('store_id', $storeId);
        request()->session()->regenerate();

        return redirect()->route('cashier.search.index');
    }

    public function jsonIndex(): JsonResponse
    {
        $user = Auth::user();
        $storeIds = $user->stores->pluck('id')->toArray();

        $queries = $this->getQueries(
            defaultFilter: [
                'id_in' => implode(',', $storeIds),
            ],
            size: -1,
        );

        $paginatedData = Store::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            StoreResponseDTO::transformCollection($paginatedData->data),
        );
    }
}
