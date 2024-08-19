<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\API\Auth\AuthResource;
use App\Http\Requests\API\Auth\AuthRequest;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendRegisterEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;

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
            $avatar = "https://res-console.cloudinary.com/cinemasway/thumbnails/v1/image/upload/v1715782888/Q0lORU1BU1dBWS9VU0VSL3ThuqNpX3h14buRbmdfd29ueHY5/drilldown";
            $token = bin2hex(random_bytes(32));
            $role = Role::where('name', 'Client')->first();
            $role_id = $role->id;
            $credential = User::query()->create([
                'role_id' => $role_id,
                'full_name' => trim($request->get('full_name')),
                'email' => trim($request->get('email')),
                'password' => Hash::make(trim($request->get('password'))),
                'phone' => trim($request->get('phone')),
                'birth_date' => trim($request->get('birth_date')),
                'gender' => trim($request->get('gender')),
                'status' => User::STATUS_INACTIVE,
                'avatar' => $avatar,
                'email_verification_token' => $token
            ]);
            $verificationUrl = route('verify-email', ['token' =>  $token]);
            if (!$credential) {
                throw new \ErrorException('Đăng ký không thành công, vui lòng thử lại', Response::HTTP_BAD_REQUEST);
            }
            SendRegisterEmail::dispatch($credential, $verificationUrl)->onQueue('emails');
            return ApiResponse(true, new AuthResource($credential), Response::HTTP_CREATED, 'Đăng ký tài khoản thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    // POST /api/account/login
    public function login(AuthRequest $request)
    {
        try {
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                if (!$user || $user->status == User::STATUS_INACTIVE) {
                    throw new \ErrorException('Thông tin không chính xác, vui lòng thử lại', Response::HTTP_BAD_REQUEST);
                }
                $token = $user->createToken('API Token')->plainTextToken;
                if ($token) {
                    $expiresAt = Carbon::now()->addMinutes(100);
                    DB::table('personal_access_tokens')
                        ->where('tokenable_id', $user->id)
                        ->update(['expires_at' => $expiresAt]);
                }
                $data = [
                    'data' => new AuthResource($user),
                    'access_token' => $token,
                ];
                return ApiResponse(true, $data, Response::HTTP_OK, 'Đăng nhập thành công');
            } else {
                throw new \ErrorException('Đăng nhập thất bại, vui lòng kiểm tra lại thông tin', Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
    //  POST /api/account/logout
    public function logout()
    {
        try {
            $userExits = Auth::user();
            if (!$userExits) {
                throw new \ErrorException('Đăng xuất thất bại', Response::HTTP_BAD_REQUEST);
            }
            $userExits->tokens()->delete();
            return ApiResponse(true, null, Response::HTTP_OK, 'Đăng xuất thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    // Get /api/account/verify
    public function verify($token)
    {
        $credential = User::where('email_verification_token', $token)->first();
        if (!$credential) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Kích hoạt không thành công,Vui lòng thực hiện đăng ký lại');
        }
        $credential->status = User::STATUS_ACTIVE;
        $credential->save();
        return  Redirect::to('http://localhost:5173/login');
    }
    public function checkTokenExpiry(Request $request)
    {
        try {
            $token = $request->user()->currentAccessToken();
            if ($token->expires_at && $token->expires_at->isPast()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Token đã hết hạn');
            }
            return ApiResponse(true,true, Response::HTTP_OK, 'token còn hạn');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }


    // Kiểm tra role của user có phải là admin hay không
    public function checkAdminRole(Request $request)
    {
        try {
            $user = $request->user();
            if ($user->role && $user->role->name === 'Admin') {
                return ApiResponse(true, null, Response::HTTP_OK, 'User là admin');
            }
            return ApiResponse(true, null, Response::HTTP_BAD_REQUEST, 'User không phải admin');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
