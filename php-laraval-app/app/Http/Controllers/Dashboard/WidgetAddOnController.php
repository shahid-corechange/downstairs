<?php

namespace App\Http\Controllers\Dashboard;

use App\DTOs\Dashboard\WidgetAddOnStatisticQueryDTO;
use App\DTOs\Dashboard\WidgetAddOnStatisticResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Addon;
use DB;
use Illuminate\Http\JsonResponse;

class WidgetAddOnController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * Get add ons statistic for widget.
     * Return the number of credit, currency and total add ons for each product.
     */
    public function statistic(WidgetAddOnStatisticQueryDTO $request): JsonResponse
    {
        $addonType = Addon::class;

        $addOns = DB::select("
            SELECT
                si.itemable_type,
                si.itemable_id,
                SUM(CASE WHEN si.payment_method = 'credit' THEN 1 ELSE 0 END) AS credit,
                SUM(CASE WHEN si.payment_method = 'invoice' THEN 1 ELSE 0 END) AS currency,
                SUM(CASE WHEN si.payment_method IN ('credit', 'invoice') THEN 1 ELSE 0 END) AS total
            FROM
                schedule_items si 
            LEFT JOIN 
                addons a  ON si.itemable_id = a.id
            LEFT JOIN 
                schedules s ON si.schedule_id = s.id
            WHERE si.itemable_type= ?
                AND s.status IN ('booked', 'progress', 'done')
                AND s.start_at BETWEEN ? AND ?
            GROUP BY
                si.itemable_type, si.itemable_id
            ORDER BY 
                si.itemable_id;
        ", [$addonType, $request->start_at, $request->end_at]);

        return $this->successResponse(
            WidgetAddOnStatisticResponseDTO::collection($addOns)
                ->only('itemableId', 'credit', 'currency', 'total', 'addon.name'),
        );
    }
}
