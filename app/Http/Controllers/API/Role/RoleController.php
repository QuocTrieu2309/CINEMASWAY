<?php

namespace App\Http\Controllers\API\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Role\RoleRequest;
use App\Http\Resources\API\Role\RoleResource;
use App\Models\Role;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/role
    public function index()
    {
        try {
            $this->authorize('checkPermission',Role::class);
            $roles = Role::where('deleted',0)->take(5)->get();
            return ApiResponse(true, RoleResource::collection($roles), Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //POST api/dashboard/role/create
    public function store(RoleRequest $request)
    {
        try {
            $this->authorize('checkPermission',Role::class);
            $role = Role::create($request->all());
            if(!$role){
               return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,messageResponseActionFailed() );
            }
            $data = [
                'role' => new RoleResource($role)
            ];
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //UPDATE api/dashboard/role/update/{id}
    public function update(RoleRequest $request, string $id){
        try {
            $this->authorize('checkPermission',Role::class);
            $role = Role::where('id',$id)->where('deleted',0)->first();
            empty($role) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $roleUpdated = Role::where('id', $id)->update([
                'name' => $request->get('name') ?? $role->name,
                'description' =>$request->description
            ]);

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }       
    }

    //DELETE api/dashboard/role/delete/{id}
    public function destroy(string $id){
        try {
            $this->authorize('checkPermission',Role::class);
            $role = Role::where('id',$id)->where('deleted',0)->first();
            empty($role) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $role->deleted = 1;
            $role->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }    
    }
}
