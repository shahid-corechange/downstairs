<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\TwoFactorLoginRequestDTO;
use App\DTOs\Auth\TwoFactorOtpRequestDTO;
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
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function login(TwoFactorLoginRequestDTO $request): RedirectResponse
    {
        $key = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'cellphone';

        if (! Auth::validate([$key => $request->user, 'password' => $request->password])) {
            return back()->withErrors([
                'user' => __('the provided credentials do not match our records'),
            ]);
        }

        /** @var \App\Models\User|null */
        $user = User::where($key, $request->user)->first();

        if ($user->info->two_factor_auth === User2FAEnum::Disabled()) {
            return back()->with('error', __('two factor authentication is disabled'));
        }

        if (! $user || ! $user->isActive() || ! $user->can(PermissionsEnum::AccessPortal())) {
            return back()->withErrors([
                'user' => __('the provided credentials do not match our records'),
            ]);
        }

        /* Validation Logic */
        $userOTP = UserOtp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('info', OTPInfoEnum::TwoFactor())
            ->first();

        if (! $userOTP) {
            return back()->with([
                'error' => __('otp is not correct'),
            ]);
        } elseif ($userOTP && now()->isAfter($userOTP->expire_at)) {
            return back()->with([
                'error' => __('otp has been expired'),
            ]);
        }

        DB::transaction(function () use ($userOTP) {
            UserOtp::where('expire_at', '<', now())->delete();
            $userOTP->delete();
        });

        /** @var Collection<int,Store> */
        $stores = $user->stores;

        // Superadmin with stores or employee with access to cashier
        if (($user->isSuperadmin() && $stores->isNotEmpty())
            || (! $user->isSuperadmin() && $user->can(PermissionsEnum::AccessCashier()))) {
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

    /**
     * Destroy an authenticated session.
     */
    public function resendOtp(TwoFactorOtpRequestDTO $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        $key = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'cellphone';

        if (! Auth::validate([$key => $request->user, 'password' => $request->password])) {
            return back()->with([
                'error' => __('the provided credentials do not match our records'),
            ]);
        }

        /** @var \App\Models\User|null */
        $user = User::where($key, $request->user)->first();

        if ($user->info->two_factor_auth === User2FAEnum::Disabled()) {
            return back()->with('error', __('two factor authentication is disabled'));
        }

        if (! $user || ! $user->isActive() || ! $user->can(PermissionsEnum::AccessPortal())) {
            return back()->with([
                'error' => __('the provided credentials do not match our records'),
            ]);
        }

        $otp = UserOtp::generate($user, OTPInfoEnum::TwoFactor());
        $recipient = $twoFactorService->getRecipient($user, $key, $request->user);

        SendOtpJob::dispatchAfterResponse($user, $otp->otp);

        return back()->with([
            'success' => __('new otp has been sent'),
            'successPayload' => [
                'action' => '2FA',
                'type' => $user->info->two_factor_auth,
                'recipient' => $recipient,
                'otpLength' => strlen($otp->otp),
            ],
        ]);
    }
}
