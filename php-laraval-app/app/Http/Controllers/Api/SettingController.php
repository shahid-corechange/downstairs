<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\UserSettingTrait;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;
    use UserSettingTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $queries = $this->getQueries(
            ['userId_eq' => Auth::id()],
            size: -1
        );
        $paginatedData = UserSetting::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            $this->castValues($paginatedData->data, true),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(): JsonResponse
    {
        $user = Auth::user();
        $data = $this->getData();
        $this->updateSettings($user, $data);

        return $this->successResponse(
            $this->castValues($user->settings->toArray(), true),
        );
    }
}
