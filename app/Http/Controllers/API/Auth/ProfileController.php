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
            $avatarCheck = "https://res-console.cloudinary.com/cinemasway/thumbnails/v1/image/upload/v1715782888/Q0lORU1BU1dBWS9VU0VSL3ThuqNpX3h14buRbmdfd29ueHY5/drilldown";
            $user = Auth::user();
            if (!$user) {
                return ApiResponse(false, null, Response::HTTP_UNAUTHORIZED, 'Vui lòng đăng nhập trước.');
            }
            $data = $request->except('avatar');
            $avatarOld = $user->avatar;
            if ($request->hasFile('avatar')) {
                $uploadedImage = Cloudinary::upload($request->file('avatar')->getRealPath(), [
                    'folder' => 'CINEMASWAY/USER',
                    'overwrite' => true,
                    'resource_type' => 'image'
                ]);
                $avatarUrl = $uploadedImage->getSecurePath();
                $data['avatar'] = $avatarUrl;
            } else {
                $data['avatar'] = $avatarOld;
            }
            $userUpdated = $user->update($data);
            if (!$userUpdated && isset($avatarUrl)) {
                $publicId = getImagePublicId($avatarUrl);
                Cloudinary::destroy($publicId);
                throw new \ErrorException('Cập nhật không thành công', Response::HTTP_BAD_REQUEST);
            }
            if ($userUpdated && isset($avatarUrl) && $avatarOld && ($avatarOld != $avatarCheck)) {
                $publicId = getImagePublicId($avatarOld);
                Cloudinary::destroy($publicId);
            }
            return ApiResponse(true, new AuthResource($user), Response::HTTP_OK, 'Cập nhật thông tin tài khoản thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}
