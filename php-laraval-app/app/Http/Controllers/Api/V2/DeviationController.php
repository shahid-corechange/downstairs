<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\Deviation\CreateDeviationRequestDTO;
use App\DTOs\Deviation\DeviationResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Deviation;
use Illuminate\Http\Response;

class DeviationController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * Store spesific resource.
     */
    public function store(CreateDeviationRequestDTO $request)
    {
        $deviation = Deviation::create([
            ...$request->toArray(),
            'user_id' => auth()->id(),
        ]);

        return $this->successResponse(
            DeviationResponseDTO::transformData($deviation),
            Response::HTTP_CREATED
        );
    }
}
