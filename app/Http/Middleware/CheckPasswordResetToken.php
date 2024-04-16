<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Response as HttpResponse;

class CheckPasswordResetToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $now = Carbon::now();
        $time90SecondsAgo = $now->subSeconds(90);
        $credential = DB::table('password_reset_tokens')->where('email',$request->email)->first();
        if(!$credential||$credential->created_at < $time90SecondsAgo){
            $credential = DB::table('password_reset_tokens')->where('email',$request->email)->delete();
            return ApiResponse(false, null, HttpResponse::HTTP_BAD_REQUEST, 'Mã token đã hết hạn.');
        }
        return $next($request);
    }
}
