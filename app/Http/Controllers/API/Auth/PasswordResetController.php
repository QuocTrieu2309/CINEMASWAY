<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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
                    'email' => 'Trường Email'
                ]
            );
            if($validator->fails()){
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, ['errors' => $validator->errors()]);
            }
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Email không tồn tại');
            }
            $token = bin2hex(random_bytes(32));
            $validToken = DB::table('password_reset_tokens')
            ->insert([
             'email'=> $request->email,
             'token' => $token,
             'created_at'=> now()
            ]);
            Mail::to($request->email)->send(new ResetPassword($token,$request->email));
            return ApiResponse(true,  $token, Response::HTTP_OK, 'Token đã được gửi tới email thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
    // POST api/account/check-token
    public function checkToken(Request $request){
        $credential = DB::table('password_reset_tokens')->where('email',$request->email)->delete();
        return ApiResponse(true,  null, Response::HTTP_OK, 'Token hợp lệ');
    }
}
