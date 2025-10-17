<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Feedback\CreateFeedbackRequestDTO;
use App\DTOs\Feedback\FeedbackResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class FeedbackUserController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateFeedbackRequestDTO $request): JsonResponse
    {
        $user = Auth::user();
        $response = $user->feedbacks()->create($request->toArray());

        return $this->successResponse(
            FeedbackResponseDTO::transformData($response),
            Response::HTTP_CREATED,
        );
    }
}
