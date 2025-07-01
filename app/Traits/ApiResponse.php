<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait ApiResponse
{
    /**
     * Success response with data
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'status_code' => $code,
        ], $code);
    }

    /**
     * Success response without data (for operations like delete, logout)
     */
    protected function successMessage(string $message = 'Operation completed successfully', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => null,
            'timestamp' => now()->toISOString(),
            'status_code' => $code,
        ], $code);
    }

    /**
     * Created response (201)
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    /**
     * No content response (204)
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message = 'An error occurred', int $code = Response::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => null,
            'timestamp' => now()->toISOString(),
            'status_code' => $code,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Not found response (404)
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Unauthorized response (401)
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Forbidden response (403)
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Conflict response (409)
     */
    protected function conflictResponse(string $message = 'Resource conflict'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_CONFLICT);
    }

    /**
     * Too many requests response (429)
     */
    protected function tooManyRequestsResponse(string $message = 'Too many requests'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Internal server error response (500)
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Service unavailable response (503)
     */
    protected function serviceUnavailableResponse(string $message = 'Service temporarily unavailable'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse($data, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more_pages' => $data->hasMorePages(),
            ],
            'timestamp' => now()->toISOString(),
            'status_code' => Response::HTTP_OK,
        ], Response::HTTP_OK);
    }

    /**
     * Collection response (for multiple items)
     */
    protected function collectionResponse($data, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return $this->successResponse($data, $message);
    }

    /**
     * Resource response (for single item)
     */
    protected function resourceResponse($data, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return $this->successResponse($data, $message);
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException(ValidationException $exception): JsonResponse
    {
        return $this->validationErrorResponse($exception->errors(), $exception->getMessage());
    }

    /**
     * Handle general exceptions
     */
    protected function handleException(\Exception $exception, string $message = null): JsonResponse
    {
        $message = $message ?? $exception->getMessage();
        
        // Log the exception for debugging
        Log::error('API Exception: ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // In production, don't expose internal errors
        if (!config('app.debug')) {
            $message = 'An unexpected error occurred';
        }

        return $this->serverErrorResponse($message);
    }
} 