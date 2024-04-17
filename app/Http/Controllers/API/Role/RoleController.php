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
            $roles = Role::take(5)->get();
            return ApiResponse(true, RoleResource::collection($roles), Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //POST api/dashboard/role/create
    public function store(RoleRequest $request)
    {
        try {
            $role = Role::create($request->all());
            if(!$role){
               return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,messageResponseActionFailed() );
            }
            $data = [
                'role' => new RoleResource($role)
            ];
            return ApiResponse(true, $request->all(), Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
