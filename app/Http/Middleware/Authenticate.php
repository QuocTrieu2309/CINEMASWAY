<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            return ApiResponse(false, null, Response::HTTP_UNAUTHORIZED, 'Bạn chưa đăng nhập.');
        }

        return ApiResponse(true, null, Response::HTTP_UNAUTHORIZED, 'Bạn đã đăng nhập.');;
    }
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
