<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\User\UpdateUserRequestDTO;
use App\DTOs\User\UserCreditResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\UserSettingTrait;
use App\Models\Service;
use Auth;
use DB;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;
    use UserSettingTrait;

    /**
     * List of additional fields to be included in the response.
     *
     * @var string[]
     */
    protected array $includes = [
        'properties.address.city.country',
        'properties.type',
        'info',
    ];

    /**
     * Display user by auth.
     */
    public function info(): JsonResponse
    {
        return $this->successResponse(
            UserResponseDTO::transformData(Auth::user(), $this->includes),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateUserByAuth(UpdateUserRequestDTO $request): JsonResponse
    {
        $user = Auth::user();
        $phones = $request->isNotOptional('cellphone') ? explode(' ', $request->cellphone) : [];
        $dialCode = $request->isNotOptional('cellphone') ? str_replace('+', '', $phones[0]) : $user->dial_code;

        DB::transaction(function () use ($request, $user, $phones, $dialCode) {
            $user->update([
                ...$request->toArray(),
                'cellphone' => $request->isNotOptional('cellphone') ? $dialCode.$phones[1] : $user->cellphone,
                'dial_code' => $dialCode,
            ]);
            $user->info->update($request->toArray());
        });

        return $this->successResponse(
            UserResponseDTO::transformData($user, $this->includes),
        );
    }

    /**
     * Display the user credit information.
     */
    public function credits(): JsonResponse
    {
        return $this->successResponse(
            UserCreditResponseDTO::transformData(Auth::user())
        );
    }

    /**
     * Display the user schedule cleanings information.
     */
    public function scheduleCleanings()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * Display frequencies of subscription for filter.
     */
    public function scheduleFrequencies()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * Display services of subscription for filter.
     */
    public function scheduleServices()
    {
        $services = Service::whereIn('id', function ($query) {
            $query->select('service_id')
                ->from('subscriptions')
                ->where('user_id', Auth::id())
                ->where('is_paused', '=', false)
                ->where('deleted_at', '=', null)
                ->where(function ($subQuery) {
                    $subQuery->where('end_at', '>=', now())
                        ->orWhereNull('end_at');
                });
        })->orderBy('id')->get();

        return $this->successResponse(
            ServiceResponseDTO::transformCollection($services)
        );
    }
}
