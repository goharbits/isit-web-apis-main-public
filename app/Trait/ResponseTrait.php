<?php

namespace App\Trait;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    public function success($data = [], $message = 'Success', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
    public function error($message = 'Error', $errors = [], $statusCode = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public function modifiedError($data = [], $message = 'Error', $errors = [], $statusCode = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'data' => $data,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
