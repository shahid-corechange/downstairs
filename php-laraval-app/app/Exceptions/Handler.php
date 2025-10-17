<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTrait;
use Arr;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Log;
use RedisException;
use Spatie\Permission\Exceptions\UnauthorizedException as PermissionException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseTrait;

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (BadRequestException $e, Request $request) {
            if (expect_json($request)) {
                return $this->errorResponse(
                    $e->getMessage() ?: __('bad request'),
                    Response::HTTP_BAD_REQUEST
                );
            }

            return Inertia::render('Error/index', [
                'code' => '400',
                'message' => __('bad request'),
            ]);
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if (expect_json($request)) {
                $message = str_starts_with($e->getMessage(), 'No query results for model')
                    ? __('not found')
                    : $e->getMessage();

                return $this->errorResponse(
                    $message ?: __('not found'),
                    Response::HTTP_NOT_FOUND
                );
            }

            return Inertia::render('Error/index', [
                'code' => '404',
                'message' => __('page not found'),
            ]);
        });

        $this->renderable(function (InvalidSignatureException $e) {
            return Inertia::render('Error/index', [
                'code' => '404',
                'message' => __('page not found'),
            ]);
        });

        $this->renderable(function (UnauthorizedException $e, $request) {
            if (expect_json($request)) {
                return $this->errorResponse(
                    $e->getMessage() ?: __('unauthorized'),
                    Response::HTTP_FORBIDDEN
                );
            }

            return redirect('/')->with('danger', __('you are not authorized to view this page'));
        });

        $this->renderable(function (PermissionException $e, $request) {
            if (expect_json($request)) {
                return $this->errorResponse(
                    $e->getMessage() ?: __('unauthorized'),
                    Response::HTTP_FORBIDDEN
                );
            }

            throw new NotFoundHttpException();
        });

        $this->renderable(function (TokenMismatchException $e, $request) {
            return redirect('/login', 303)->with('error', __('session expired'));
        });

        $this->renderable(function (HttpException $e, $request) {
            if (expect_json($request)) {
                return $this->errorResponse(
                    $e->getMessage(),
                    $e->getStatusCode()
                );
            }

            // Handle 419 page expired
            if ($e->getStatusCode() === 419) {
                return redirect('/login', 303)->with('error', __('session expired'));
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return $this->errorResponse(
                    $e->getMessage() ?: __('unauthenticated'),
                    Response::HTTP_UNAUTHORIZED
                );
            }

            return redirect('/login');
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if (expect_json($request)) {
                return $this->errorResponse(__('method not allowed'), Response::HTTP_METHOD_NOT_ALLOWED);
            }

            return Inertia::render('Error/index', [
                'code' => '405',
                'message' => __('method not allowed'),
            ]);
        });

        $this->renderable(function (ThrottleRequestsException $e, $request) {
            if (expect_json($request)) {
                return $this->errorResponse(__('too many requests'), Response::HTTP_TOO_MANY_REQUESTS);
            }

            return back(303)->with('error', __('too many requests'));
        });

        $this->renderable(function (ValidationException $e, Request $request) {
            if (expect_json($request)) {
                return $this->errorResponse(
                    $e->getMessage() ?: __('validation error'),
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    errors: $e->errors()
                );
            }

            return $this->convertValidationExceptionToResponse($e, $request);
        });

        $this->renderable(function (ServiceUnavailableHttpException $e, Request $request) {
            if (expect_json($request)) {
                return $this->errorResponse(
                    $e->getMessage() ?: __('service unavailable'),
                    Response::HTTP_SERVICE_UNAVAILABLE,
                );
            }

            return Inertia::render('Error/index', [
                'code' => '503',
                'message' => __('service unavailable'),
            ]);
        });

        $this->renderable(function (Throwable $e, $request) {
            Log::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (expect_json($request)) {
                return $this->errorResponse(
                    $e->getMessage() ?: __('internal server error'),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            return Inertia::render('Error/index', [
                'code' => '500',
                'message' => __('internal server error'),
            ]);
        });

        $this->reportable(function (Throwable $e) {
            /**
             * Ignore Redis read error on connection
             * when queue worker is trying to read empty data.
             */
            if ($e instanceof RedisException && str_contains($e->getMessage(), 'read error on connection')) {
                return false;
            }

            return true;
        });
    }

    /**
     * Convert a validation exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    protected function invalid($request, ValidationException $exception)
    {
        return redirect($exception->redirectTo ?? url()->previous())
            ->withInput(Arr::except($request->input(), $this->dontFlash))
            ->withErrors(
                array_keys_to_camel_case($exception->errors()),
                $request->input('_error_bag', $exception->errorBag)
            );
    }
}
