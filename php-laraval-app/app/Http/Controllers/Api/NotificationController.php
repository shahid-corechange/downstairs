<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Notification\NotificationResponseDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Notification;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $app = request()->header('X-Requested-By');
        $queries = $this->getQueries([
            'userId_equal' => $user->id,
            'hub_equal' => $app === 'Customer' && $user->can(PermissionsEnum::AccessCustomerApp()) ?
                NotificationHubEnum::Customer() : NotificationHubEnum::Employee(),
        ]);
        $paginatedData = Notification::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            NotificationResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Read a notification
     */
    public function read(Notification $notification): Response
    {
        $this->authorize('update', $notification);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return response()->noContent();
    }

    /**
     * Read all notification
     */
    public function readAll(): Response
    {
        $app = request()->header('X-Requested-By');
        $hub = $app === 'Customer' && Auth::user()->can(PermissionsEnum::AccessCustomerApp()) ?
            NotificationHubEnum::Customer() : NotificationHubEnum::Employee();

        Notification::where('user_id', Auth::id())
            ->where('hub', $hub)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->noContent();
    }
}
