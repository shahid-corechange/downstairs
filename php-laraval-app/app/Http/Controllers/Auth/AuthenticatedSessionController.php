<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\LoginPortalRequestDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\Enums\Auth\OTPInfoEnum;
use App\Enums\PermissionsEnum;
use App\Enums\User\User2FAEnum;
use App\Http\Controllers\Controller;
use App\Jobs\SendOtpJob;
use App\Models\Store;
use App\Models\User;
use App\Models\UserOtp;
use App\Providers\RouteServiceProvider;
use App\Services\TwoFactorService;
use Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function index(): Response
    {
        return Inertia::render('Login/index');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginPortalRequestDTO $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        $key = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'cellphone';

        if (! Auth::validate([$key => $request->user, 'password' => $request->password])) {
            return back()->withErrors([
                'user' => __('the provided credentials do not match our records'),
            ]);
        }

        /** @var \App\Models\User|null */
        $user = User::where($key, $request->user)->first();

        if (! $user || ! $user->isActive() || ! $user->can(PermissionsEnum::AccessPortal())) {
            return back()->withErrors([
                'user' => __('the provided credentials do not match our records'),
            ]);
        }

        if ($user->info->two_factor_auth === User2FAEnum::Disabled()) {
            /** @var Collection<int,Store> */
            $stores = $user->stores;

            // Superadmin with stores or employee with access to cashier
            if (($user->isSuperadmin() && $stores->isNotEmpty()) ||
                (! $user->isSuperadmin() && $user->can(PermissionsEnum::AccessCashier()))) {
                if ($stores->isEmpty()) {
                    return back()->withErrors([
                        'user' => __('no stores found'),
                    ]);
                }

                return back()->with([
                    'successPayload' => [
                        'stores' => StoreResponseDTO::transformCollection($stores),
                    ],
                ]);
            }

            Auth::login($user, $request->remember);
            request()->session()->regenerate();

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $otp = UserOtp::generate($user, OTPInfoEnum::TwoFactor());
        $recipient = $twoFactorService->getRecipient($user, $key, $request->user);

        SendOtpJob::dispatchAfterResponse($user, $otp->otp);

        return back()->with([
            'successPayload' => [
                'action' => '2FA',
                'type' => $user->info->two_factor_auth,
                'recipient' => $recipient,
                'otpLength' => strlen($otp->otp),
            ],
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cache::forget('user_'.Auth::id());

        return redirect(RouteServiceProvider::HOME);
    }
}
