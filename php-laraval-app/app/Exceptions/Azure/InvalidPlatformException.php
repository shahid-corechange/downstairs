<?php

namespace App\Exceptions\Azure;

use App\Http\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Response;

class InvalidPlatformException extends Exception
{
    use ResponseTrait;

    public function report(): bool
    {
        return false;
    }

    public function render($request)
    {
        if (expect_json($request)) {
            return $this->errorResponse($this->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return back()->with('error', $this->getMessage());
    }
}
