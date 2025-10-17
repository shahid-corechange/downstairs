<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTrait;
use Exception;

class OperationFailedException extends Exception
{
    use ResponseTrait;

    public function render($request)
    {
        if (expect_json($request)) {
            return $this->errorResponse($this->getMessage(), $this->getCode());
        }

        return back()->with('error', $this->getMessage());
    }
}
