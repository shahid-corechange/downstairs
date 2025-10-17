<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Auth\ForgetPasswordRequestDTO;
use App\DTOs\Auth\LoginByEmailRequestDTO;
use App\DTOs\Auth\LoginResponseDTO;
use App\DTOs\Auth\LogoutRequestDTO;
use App\DTOs\Auth\RefreshResponseDTO;
use App\Enums\Notification\NotificationRegistrationActionEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\NotificationRegistrationManagerJob;
use App\Models\User;
use DB;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ResponseTrait;

    /**
     * Handle an incoming api authentication request.
     *
     * @throws ValidationException
     */
    public function login(LoginByEmailRequestDTO $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        $app = request()->header('X-Requested-By');

        if ($app === 'Customer') {
            $permission = PermissionsEnum::AccessCustomerApp();
        } elseif ($app === 'Worker') {
            $permission = PermissionsEnum::AccessEmployeeApp();
        } else {
            $permission = PermissionsEnum::AccessPortal();
        }

        if (! Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'email' => [__('the provided credentials are incorrect')],
            ]);
        } elseif (! $app || ! $user->can($permission)) {
            event(new Failed('api', $user, ['app' => $app]));

            throw new UnauthorizedException();
        }

        $tokens = $user->generateTokens();

        if ($request->isNotOptional('device_token')) {
            NotificationRegistrationManagerJob::dispatchAfterResponse(
                $user,
                NotificationRegistrationActionEnum::Register(),
                $request->device_platform,
                $request->device_token,
            );
        }

        return $this->successResponse(LoginResponseDTO::from($tokens));
    }

    /**
     * Handle an incoming api password reset link request.
     */
    public function resetPassword(ForgetPasswordRequestDTO $request)
    {
        $status = Password::sendResetLink([
            'email' => $request->email,
        ]);

        return $status == Password::RESET_LINK_SENT
            ? response()->noContent()
            : $this->errorResponse(__('password reset email failed to sent'), 554);
    }

    /**
     * Handle log out request.
     */
    public function logout(LogoutRequestDTO $request): Response
    {
        $user = Auth::user();

        if ($request->isNotOptional('device_token')) {
            NotificationRegistrationManagerJob::dispatchAfterResponse(
                $user,
                NotificationRegistrationActionEnum::Unregister(),
                $request->device_platform,
                $request->device_token,
            );
        }

        $user->revokeTokens();

        event(new Logout('api', $user));

        return response()->noContent();
    }

    /**
     * Handle an incoming api refresh token request.
     */
    public function refresh(): JsonResponse
    {
        $user = Auth::user();

        $tokens = DB::transaction(function () use ($user) {
            $tokens = $user->generateTokens();
            $user->revokeTokens();

            return $tokens;
        });

        return $this->successResponse(RefreshResponseDTO::from($tokens));
    }
}
