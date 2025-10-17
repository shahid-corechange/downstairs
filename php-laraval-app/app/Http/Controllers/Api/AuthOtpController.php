<?php

namespace App\Http\Controllers\Api;

use App\Contracts\SMSService;
use App\DTOs\Auth\GenerateOtpRequestDTO;
use App\DTOs\Auth\GenerateOtpResponseDTO;
use App\DTOs\Auth\LoginByOtpRequestDTO;
use App\DTOs\Auth\LoginResponseDTO;
use App\Enums\Auth\OTPInfoEnum;
use App\Enums\Notification\NotificationRegistrationActionEnum;
use App\Enums\PermissionsEnum;
use App\Helpers\SMS\SMSTemplate;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\NotificationRegistrationManagerJob;
use App\Models\User;
use App\Models\UserOtp;
use DB;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class AuthOtpController extends Controller
{
    use ResponseTrait;

    /**
     * Generate OTP token with with given cellphone number.
     */
    public function generate(GenerateOtpRequestDTO $request, SMSService $smsService): JsonResponse
    {
        $user = User::where('cellphone', $request->cellphone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'cellphone' => [__('cellphone is not registered')],
            ]);
        }

        /* Generate An OTP */
        $userOTP = UserOtp::generate($user, OTPInfoEnum::Login());

        //$smsService->personalize(SMSTemplate::OTP_TEMPLATE, $userOTP->otp)->send($request->cellphone);

        $appEnv = config('app.env');
        $response = GenerateOtpResponseDTO::from([
            'cellphone' => $request->cellphone,
            'otp' => $appEnv == 'production' ? null : $userOTP->otp,
            'expire_at' => $userOTP->expire_at,
        ]);

        return $this->successResponse($response, Response::HTTP_CREATED);
    }

    /**
     * Handling authentication with OTP.
     */
    public function login(LoginByOtpRequestDTO $request): JsonResponse
    {
        $app = request()->header('X-Requested-By');
        $user = User::where('cellphone', $request->cellphone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'cellphone' => [__('cellphone is not registered')],
            ]);
        }

        /* Validation Logic */
        $userOTP = UserOtp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('info', OTPInfoEnum::Login())
            ->first();

        if ($app === 'Customer') {
            $permission = PermissionsEnum::AccessCustomerApp();
        } elseif ($app === 'Worker') {
            $permission = PermissionsEnum::AccessEmployeeApp();
        } else {
            $permission = PermissionsEnum::AccessPortal();
        }

        if (! $userOTP) {
            event(new Failed('api', $user, ['otp' => $request->otp]));

            throw ValidationException::withMessages([
                'otp' => [__('otp is not correct')],
            ]);
        } elseif ($userOTP && now()->isAfter($userOTP->expire_at)) {
            event(new Failed('api', $user, ['otp' => $request->otp]));

            throw ValidationException::withMessages([
                'otp' => [__('otp has been expired')],
            ]);
        } elseif (! $permission || ! $user->can($permission)) {
            event(new Failed('api', $user, ['app' => $app]));

            throw new UnauthorizedException();
        } elseif ($app === 'Customer' && $user->subscriptions()->withTrashed()->count() === 0) {
            throw new UnauthorizedException(
                __('you do not have any subscription'),
            );
        }

        $tokens = DB::transaction(function () use ($user, $userOTP) {
            if (! $user->cellphone_verified_at) {
                $user->update(['cellphone_verified_at' => now()]);
            }

            //UserOtp::where('expire_at', '<', now())->delete();
            //$userOTP->delete();

            return $user->generateTokens();
        });

        if ($request->isNotOptional('device_token')) {
            NotificationRegistrationManagerJob::dispatchAfterResponse(
                $user,
                NotificationRegistrationActionEnum::Register(),
                $request->device_platform,
                $request->device_token,
            );
        }

        event(new Login('api', $user, false));

        return $this->successResponse(LoginResponseDTO::from($tokens));
    }
}
