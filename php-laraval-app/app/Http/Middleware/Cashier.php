<?php

namespace App\Http\Middleware;

use App\Enums\PermissionsEnum;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Cashier
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $storeId = $request->session()->get('store_id');
        $user = Auth::user();

        // If the user is not a cashier or the store is not selected, redirect to the home page
        // If the user doesn't have access to the store, redirect to the home page
        if (! $user->can(PermissionsEnum::AccessCashier()) || ! $storeId || ! $user->stores->contains($storeId)) {
            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
