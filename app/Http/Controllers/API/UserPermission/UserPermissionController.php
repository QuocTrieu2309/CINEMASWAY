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
}
