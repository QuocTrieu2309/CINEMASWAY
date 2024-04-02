<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('check.token.expiration');
    }
    //Get api/account/profile
    public function getProfile()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw new \ErrorException('Không tìm thấy người dùng', Response::HTTP_EXPECTATION_FAILED);
            }
            return ApiResponse(true, $user, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}
