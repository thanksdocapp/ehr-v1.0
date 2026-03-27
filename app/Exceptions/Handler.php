<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
     * Convert an authentication exception into a response (never leak raw exception text in JSON).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->shouldReturnJson($request, $exception)
            ? response()->json([
                'message' => __('errors.unauthenticated'),
                'success' => false,
            ], 401)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
            'success' => false,
        ], $exception->status);
    }

    /**
     * Determine if the exception handler response should be JSON.
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return parent::shouldReturnJson($request, $e)
            || $request->is('api/*')
            || $request->ajax();
    }

    /**
     * Prepare a JSON response for the given exception (user-friendly; technical details only in logs via report()).
     */
    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return $this->sanitizedJsonResponse($request, $e);
    }

    protected function sanitizedJsonResponse(Request $request, Throwable $e): JsonResponse
    {
        $status = 500;
        $headers = [];

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $headers = $e->getHeaders();
        }

        if ($e instanceof QueryException) {
            $status = 500;
        }

        $message = $this->friendlyApiMessage($e, $status);

        return new JsonResponse(
            [
                'message' => $message,
                'success' => false,
            ],
            $status,
            $headers,
            JSON_UNESCAPED_SLASHES
        );
    }

    protected function friendlyApiMessage(Throwable $e, int $status): string
    {
        if ($e instanceof QueryException) {
            return __('errors.server_error');
        }

        return match ($status) {
            401 => __('errors.unauthenticated'),
            403 => __('errors.forbidden'),
            404 => __('errors.not_found'),
            405 => __('errors.method_not_allowed'),
            410 => __('errors.gone'),
            419 => __('errors.page_expired'),
            422 => __('errors.generic_client_error'),
            429 => __('errors.too_many_requests'),
            503 => __('errors.service_unavailable'),
            default => $status >= 500
                ? __('errors.server_error')
                : __('errors.generic_client_error'),
        };
    }
}
