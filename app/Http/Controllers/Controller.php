<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function successResponse($data, string $message = 'Operation successful', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    protected function errorResponse(string $error, string $code = 'ERROR', int $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'error' => $error,
            'code' => $code,
        ], $statusCode);
    }
}
