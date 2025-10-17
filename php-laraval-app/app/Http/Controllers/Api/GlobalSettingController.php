<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GlobalSettingTrait;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\GlobalSetting;
use Illuminate\Http\JsonResponse;

class GlobalSettingController extends Controller
{
    use GlobalSettingTrait;
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $queries = $this->getQueries(size: -1);
        $paginatedData = GlobalSetting::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            $this->castValues($paginatedData->data),
        );
    }
}
