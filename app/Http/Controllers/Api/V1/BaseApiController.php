<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BaseApiController extends Controller
{
    /**
     * Success response method.
     */
    public function sendResponse($result, $message = 'Success', $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result,
            'timestamp' => now()->toISOString(),
        ];

        return response()->json($response, $code);
    }

    /**
     * Error response method.
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
            'timestamp' => now()->toISOString(),
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Validation error response method.
     */
    public function sendValidationError($validator): JsonResponse
    {
        return $this->sendError('Validation Error', $validator->errors(), 422);
    }

    /**
     * Unauthorized response method.
     */
    public function sendUnauthorized($message = 'Unauthorized'): JsonResponse
    {
        return $this->sendError($message, [], 401);
    }

    /**
     * Forbidden response method.
     */
    public function sendForbidden($message = 'Forbidden'): JsonResponse
    {
        return $this->sendError($message, [], 403);
    }

    /**
     * Server error response method.
     */
    public function sendServerError($message = 'Internal Server Error'): JsonResponse
    {
        return $this->sendError($message, [], 500);
    }

    /**
     * Paginated response method.
     */
    public function sendPaginatedResponse($data, $message = 'Success'): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more_pages' => $data->hasMorePages(),
            ],
            'timestamp' => now()->toISOString(),
        ];

        return response()->json($response, 200);
    }

    /**
     * Resource not found response.
     */
    public function sendNotFound($resource = 'Resource'): JsonResponse
    {
        return $this->sendError("{$resource} not found", [], 404);
    }

    /**
     * Resource created response.
     */
    public function sendCreated($data, $message = 'Resource created successfully'): JsonResponse
    {
        return $this->sendResponse($data, $message, 201);
    }

    /**
     * Resource updated response.
     */
    public function sendUpdated($data, $message = 'Resource updated successfully'): JsonResponse
    {
        return $this->sendResponse($data, $message, 200);
    }

    /**
     * Resource deleted response.
     */
    public function sendDeleted($message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->sendResponse(null, $message, 200);
    }
}
