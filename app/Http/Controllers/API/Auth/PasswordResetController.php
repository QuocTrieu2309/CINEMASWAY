<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.token.passwordReset')->only('checkToken');
    }
    //POST /api/account/forgot-password
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                ['email' => 'required|email'],
                [
                    'required' => 'Trường :attribute Không được để trống',
                    'email' => 'Trường :attribute không đúng định dạng'
                ],
                [
                    'email' => 'Email'
                ]
            );
            if ($validator->fails()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, ['errors' => $validator->errors()]);
            }
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Email không tồn tại');
            }
            $token = bin2hex(random_bytes(32));
            $validToken = DB::table('password_reset_tokens')
                ->insert([
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => now()
                ]);
            Mail::to($request->email)->send(new ResetPassword($token, $request->email));
            return ApiResponse(true,  $token, Response::HTTP_OK, 'Token đã được gửi tới email thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
    // POST api/account/check-token
    public function checkToken(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'password' => 'required|string|min:8|max:10',
                ],
                [
                    'required' => 'Trường :attribute không được để trống',
                    'string' => 'Trường :attribute phải là chuỗi kí tự',
                    'min' => 'Trường :attribute có độ dài tối thiểu :min kí tự',
                    'max' => 'Trường :attribute có độ dài tối đa là :max kí tự',
                ],
                [
                    'password' => 'Mật khẩu mới',
                ]
            );
    
            if ($validator->fails()) {
                $credential = DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, ['errors' => $validator->errors()]);
            }
    
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $credential = DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                return ApiResponse(true,  null, Response::HTTP_OK, 'Email không tồn tại');
            }
    
            $user->password = Hash::make(trim($request->password));
            $user->save();
    
            $credential = DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return ApiResponse(true, $user, Response::HTTP_OK, 'Reset password thành công, vui lòng đăng nhập lại để sử dụng dịch vụ');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}
