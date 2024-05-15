<?php

namespace App\Http\Controllers\API\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Role\RoleRequest;
use App\Http\Resources\API\Role\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/role
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Role::class);
            $this->limit == $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Role::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'roles' => RoleResource::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ]
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    
    // GET /api/translation/{id}
    public function show($id)
    {

        try {
            $this->authorize('checkPermission', Role::class);
            $role = Role::where('id', $id)->where('deleted', 0)->first();
            empty($role) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'screen' => new  RoleResource($role),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //POST api/dashboard/role/create
    public function store(RoleRequest $request)
    {
        try {
            $this->authorize('checkPermission', Role::class);
            $role = Role::create($request->all());
            if (!$role) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
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
    public function update(RoleRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', Role::class);
            $role = Role::where('id', $id)->where('deleted', 0)->first();
            empty($role) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $roleUpdated = Role::where('id', $id)->update([
                'name' => $request->get('name') ?? $role->name,
                'description' => $request->description
            ]);

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //DELETE api/dashboard/role/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Role::class);
            $role = Role::where('id', $id)->where('deleted', 0)->first();
            empty($role) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $role->deleted = 1;
            $role->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
