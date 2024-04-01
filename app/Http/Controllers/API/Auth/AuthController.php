<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\API\Auth\AuthResource;
use App\Http\Requests\API\Auth\AuthRequest;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendRegisterEmail;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except([
            'login',
            'register',
        ]);
    }

    //  POST /api/account/register
    public function register(AuthRequest $request)
    {
        try {
            $credential = User::query()->create([
                'full_name' => trim($request->get('full_name')),
                'role_id' => trim($request->get('role_id')),
                'email' => trim($request->get('email')),
                'password' => Hash::make(trim($request->get('password'))),
                'phone' => trim($request->get('phone')),
                'birth_date' => trim($request->get('birth_date')),
                'gender' => trim($request->get('gender')),
                'role_id' => trim($request->get('role_id')),
            ]);
            if (!$credential) {
                throw new \ErrorException('Đăng ký không thành công, vui lòng thử lại', Response::HTTP_BAD_REQUEST);
            }
            SendRegisterEmail::dispatch($credential)->onQueue('emails');
            return ApiResponse(true, new AuthResource($credential), Response::HTTP_CREATED, 'Đăng ký tài khoản thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}
