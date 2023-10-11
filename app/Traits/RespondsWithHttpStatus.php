<?php

namespace App\Traits;

trait RespondsWithHttpStatus
{
    protected function success($message, $data = [], $status = 200)
    {
        error_log(json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ]));

        return response([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    protected function failure($message, $data = [], $status = 422)
    {
        error_log(json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ]));

        return response([
            'success' => false,
            'data' => $data,
            'message' => $message,
        ], $status);
    }
}
