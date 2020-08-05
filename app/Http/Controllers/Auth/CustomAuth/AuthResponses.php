<?php

namespace App\Http\Controllers\Auth\CustomAuth;

trait AuthResponses
{
    /**
     * Return response message.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getResponses(array $data, int $httpCode = 200)
    {
        return response()->json($data, $httpCode);
    }
}
