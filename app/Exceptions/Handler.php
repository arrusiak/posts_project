<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof AuthenticationException) {
            $statusCode = Response::HTTP_UNAUTHORIZED;
        } elseif ($exception instanceof ValidationException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            Log::error($exception->getMessage(), $exception->errors());
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => $statusCode,
                'errors' => $exception->errors()
            ], $statusCode);
        } elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = Response::HTTP_NOT_FOUND;
        }

        // catch all of the exceptions and format the errors for us.
        if ($statusCode >= 400 && $statusCode <= 599) {
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => (int)$statusCode,
            ], $statusCode);
        }

        return parent::render($request, $exception);
    }
}
