<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTrait;
use Exception;

class InvalidSMSException extends Exception
{
    use ResponseTrait;

    public function report(): bool
    {
        return false;
    }

    public function render($request)
    {
        return $this->errorResponse($this->getMessage(), $this->getCode());
    }
}
