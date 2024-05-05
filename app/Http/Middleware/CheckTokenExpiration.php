<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;

class CheckTokenExpiration
{
    public function handle($request, Closure $next)
    {
        // Kiểm tra xem người dùng đã đăng nhập hay chưa
        if (Auth::check()) {
            $user = Auth::user();
            $currentAccessToken = $user->currentAccessToken();

            if (!$currentAccessToken || $currentAccessToken->expires_at < now()) {
                // Xóa các token cũ
                $user->tokens()->delete(); 
                return ApiResponse(false, null, HttpResponse::HTTP_BAD_REQUEST, 'Mã token đã hết hạn.');
            }
        }

        return $next($request);
    }
}
