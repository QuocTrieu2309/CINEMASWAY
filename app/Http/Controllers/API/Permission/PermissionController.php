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
            $permissions = Permission::take(5)->get();
            return ApiResponse(true, PermissionResource::collection($permissions), Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
