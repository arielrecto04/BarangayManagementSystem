<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthenticationService;

class AuthenticationController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        return app(AuthenticationService::class)->login($credentials);
    }

    public function logout(Request $request)
    {
        return app(AuthenticationService::class)->logout($request);
    }
}
