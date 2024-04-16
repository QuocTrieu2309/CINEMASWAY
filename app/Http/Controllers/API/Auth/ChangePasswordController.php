<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\API\Auth\AuthResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangePassword;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //POST api/account/active-token
    public function activeToken()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponse(false, null, Response::HTTP_UNAUTHORIZED, 'Vui lòng đăng nhập trước.');
            }
            $email = $user->email;
            $token = bin2hex(random_bytes(32));
            $validToken = DB::table('password_reset_tokens')
                ->insert([
                    'email' => $email,
                    'token' => $token,
                    'created_at' => now()
                ]);
            Mail::to($email)->send(new ChangePassword($token, $email));
            return ApiResponse(true,  $token, Response::HTTP_OK, 'Token đã được gửi tới email thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
    //POSt api/account/change-password
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'password' => 'required|string|min:8|max:10',
                    'new_password' => 'required|string|min:8|max:10'
                ],
                [
                    'required' => 'Trường :attribute không được để trống',
                    'string' => 'Trường :attribute phải là chuỗi kí tự',
                    'min' => 'Trường :attribute có độ dài tối thiều :min kí tự',
                    'max' => 'Trường :attibute có độ dài tối đa là :max kí tự',
                    'different' => 'Mật khẩu mới không được trùng với mật khẩu cũ'
                ],
                [
                    'password' => 'Mật khẩu cũ',
                    'new_password' => 'Mật khẩu mới'
                ]
            );
            if ($validator->fails()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, ['errors' => $validator->errors()]);
            }
            if (!Hash::check($request->password, Auth::user()->password)) {
                throw new \ErrorException('Mật khẩu cũ không chính xác', Response::HTTP_UNAUTHORIZED);
            }
            $user = Auth::user();
            $user->password = Hash::make(trim($request->new_password));
            $user->save();
            return ApiResponse(true, new AuthResource($user), Response::HTTP_OK, 'Đổi mật khẩu thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}
