<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ValidateCodeActivation extends Controller
{
    /**
     * Validate code activation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function __invoke(Request $request)
    {
        $user = User::whereEmail(base64url_decode($request->e))->first();

        if (! $user) {
            throw new \Illuminate\Auth\AuthenticationException;
        }

        if ($user->getActivationCode() != $request->act_code) {
            return response()->json([
                'message' => 'Kode yang Anda masukkan salah',
                'success' => false,
            ], 422);
        }

        return response()->json([
            'message' => 'Code is valid',
            'success' => true,
        ], 200);
    }
}
