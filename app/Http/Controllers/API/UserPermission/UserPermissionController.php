<?php

namespace App\Http\Controllers\API\UserPermission;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\UserPermission\UserPermissionRequest;
use App\Http\Resources\API\UserPermission\UserPermissionResource;
use App\Models\UserPermission;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class UserPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/user-permission
    public function index(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = UserPermission::orderBy($this->sort, $this->order)->paginate($this->limit);
            return ApiResponse(true, UserPermissionResource::collection($data), Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //POST api/dashboard/user-permission/create
    public function store(UserPermissionRequest $request)
    {
        try {
            $credential = UserPermission::where('user_id',$request->user_id)
                                        ->where('permission_id',$request->permission_id)->first();
            if($credential){
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,'Quyền hạn của người dùng đã tồn tại.' );
            }
            $userPermission = UserPermission::create($request->all());
            if(!$userPermission){
               return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,messageResponseActionFailed() );
            }
            $data = [
                'userPermission' => new UserPermissionResource($userPermission)
            ];
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //UPDATE api/dashboard/user-permission/update/{id}
    public function update(UserPermissionRequest $request, string $id){
        try {
            $userPermission = UserPermission::find($id);
            empty($userPermission) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $credential = UserPermission::where('user_id',$request->user_id)
                                        ->where('permission_id',$request->permission_id)
                                        ->where('id','!=',$id)
                                        ->first();
            if($credential){
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,'Quyền hạn của người dùng đã tồn tại.' );
            }
            $permissionUpdated = UserPermission::where('id', $id)->update([
                'user_id' => $request->user_id,
                'permission_id' =>$request->permission_id
            ]);
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }       
    }
}
