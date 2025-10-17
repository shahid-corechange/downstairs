<?php

namespace App\Exceptions\Azure;

use App\Http\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Response;

class InvalidConnectionStringException extends Exception
{
    use ResponseTrait;

    public function report(): bool
    {
        return false;
    }

    public function render($request)
    {
        if (expect_json($request)) {
            return $this->errorResponse($this->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return back()->with('error', $this->getMessage());
    }
}
