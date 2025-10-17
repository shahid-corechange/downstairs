<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Response;

class ErrorResponseException extends Exception
{
    use ResponseTrait;

    public function __construct(
        $message = '',
        $code = Response::HTTP_BAD_REQUEST,
        protected array $headers = [],
        protected array $errors = []
    ) {
        parent::__construct($message, $code);
    }

    public function report(): bool
    {
        return false;
    }

    /**
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function render($request)
    {
        if (expect_json($request)) {
            return $this->errorResponse(
                $this->getMessage(),
                $this->getCode(),
                $this->headers,
                $this->errors
            );
        }

        return back()->with([
            ...$this->errors,
            'error' => $this->getMessage(),
        ]);
    }
}
