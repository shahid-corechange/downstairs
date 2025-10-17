<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Response;

class CustomValidationException extends Exception
{
    use ResponseTrait;

    protected $errors;

    protected $headers;

    public function __construct($message, array $errors = [], array $headers = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
        $this->headers = $headers;
    }

    public function report(): bool
    {
        return false;
    }

    public function render($request)
    {
        if (expect_json($request)) {
            return $this->errorResponse(
                $this->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->headers,
                $this->errors
            );
        }

        return back()->with('error', $this->getMessage());
    }
}
