<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Bridge\RefreshToken;

class LoginController extends Controller
{
    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $success['token'] = Auth::user()->accessToken;

            return new JsonResponse(['success' => $success], Response::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Invalid email/password pair'], Response::HTTP_UNAUTHORIZED);
    }

    public function logout()
    {
        /* TODO */
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $accessToken = Auth::user()->token();
            $accessToken->revoke();
            Auth::logout();

            return new JsonResponse(['success' => 'Token revoked'], Response::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Invalid email/password pair'], Response::HTTP_UNAUTHORIZED);
    }
}
