<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\Auth\AuthResource;
use App\Http\Requests\API\Auth\AuthRequest;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

    //POST api/account/profile/update

    public function updateProfile(AuthRequest $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponse(false, null, Response::HTTP_UNAUTHORIZED, 'Vui lòng đăng nhập trước.');
            }
            $data = $request->except('avatar');
            if ($request->hasFile('avatar')) {
                $avatarUrl = Cloudinary::upload($request->file('avatar')->getRealPath(), array(
                    'folder' => 'CINEMASWAY/USER',
                    'overwrite' => TRUE,
                    'resource_type' => 'image'
                ))->getSecurePath();
                $data['avatar'] = $avatarUrl;
            } else {
                $avatarOld = $user->avatar;
                $data['avatar'] = $avatarOld;
            }
            $user->full_name = $data['full_name'];
            $user->phone = $data['phone'];
            $user->gender = $data['gender'];
            $user->birth_date = $data['birth_date'];
            $user->avatar = $data['avatar'];
            $userUpdated = $user->save();
            if (!$userUpdated) {
                $publicId = $this->getImagePublicId($avatarUrl);
                Cloudinary::destroy($publicId);
                throw new \ErrorException('Cập nhật không thành công', Response::HTTP_BAD_REQUEST);
            }
            return ApiResponse(true, new AuthResource($user), Response::HTTP_OK, 'Cập nhật thông tin tài khoản thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}
