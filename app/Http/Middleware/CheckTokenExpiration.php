<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Response as HttpResponse;

class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra xem người dùng đã đăng nhập và token có hết hạn hay không
        
        if (Auth::user() && Auth::user()->token()->expires_at < now()) {
            Auth::user()->tokens()->delete(); // Xóa các token cũ
            return ApiResponse(false, null, HttpResponse::HTTP_BAD_REQUEST, 'Mã token đã hết hạn.');
        }

        return $next($request);
    }
}
