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
}
