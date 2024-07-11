<?php

namespace App\Http\Controllers\API\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Permission\PermissionRequest;
use App\Http\Resources\API\Permission\PermissionResource;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/permission
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Permission::class);
            $this->limit == $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Permission::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'permissions' =>PermissionResource::collection($data),
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
            $this->authorize('checkPermission', Permission::class);
            $permission = Permission::where('id', $id)->where('deleted', 0)->first();
            empty($permission) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'screen' => new  PermissionResource($permission),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }


    //POST api/dashboard/permission/create
    public function store(PermissionRequest $request)
    {
        try {
            $this->authorize('checkPermission', Permission::class);
            $permission = Permission::create($request->all());
            if (!$permission) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
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
    public function update(PermissionRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', Permission::class);
            $permission = Permission::where('id', $id)->where('deleted', 0)->first();
            empty($permission) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $permissionUpdated = Permission::where('id', $id)->update([
                'name' => $request->get('name') ?? $permission->name,
                'description' => $request->description
            ]);
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //DELETE api/dashboard/permission/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Permission::class);
            DB::beginTransaction();
            $permission = Permission::where('id', $id)->where('deleted', 0)->first();
            empty($permission) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $hasRelatedRecords =$permission->userPermissions()->exists();
            if ($hasRelatedRecords) {
                $permission->deleted = 1;
                $permission->save();
            } else {
                $permission->delete();
            }
            DB::commit();            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
