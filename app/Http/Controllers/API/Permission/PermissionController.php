<?php

namespace App\Http\Controllers\API\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Permission\PermissionRequest;
use App\Http\Resources\API\Permission\PermissionResource;
use App\Models\Permission;
use Illuminate\Http\Response;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/permission
    public function index()
    {
        try {
            $this->authorize('checkPermission',Permission::class);
            $permissions = Permission::where('deleted',0)->take(5)->get();
            return ApiResponse(true, PermissionResource::collection($permissions), Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    
    //POST api/dashboard/permission/create
    public function store(PermissionRequest $request)
    {
        try {
            $this->authorize('checkPermission',Permission::class);
            $permission = Permission::create($request->all());
            if(!$permission){
               return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,messageResponseActionFailed() );
            }
            $data = [
                'permission' => new PermissionResource($permission)
            ];
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //UPDATE api/dashboard/permission/update/{id}
    public function update(PermissionRequest $request, string $id){
        try {
            $this->authorize('checkPermission',Permission::class);
            $permission = Permission::where('id',$id)->where('deleted',0)->first();
            empty($permission) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $permissionUpdated = Permission::where('id', $id)->update([
                'name' => $request->get('name') ?? $permission->name,
                'description' =>$request->description
            ]);
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }       
    }

    //DELETE api/dashboard/permission/delete/{id}
    public function destroy(string $id){
        try {
            $this->authorize('checkPermission',Permission::class);
            $permission = Permission::where('id',$id)->where('deleted',0)->first();
            empty($permission) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $permission->deleted = 1;
            $permission->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }    
    }
}
