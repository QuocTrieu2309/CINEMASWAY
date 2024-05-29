<?php

namespace App\Http\Controllers\API\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Service\ServiceRequest;
use App\Http\Resources\Api\Service\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    //GET api/dashboard/services
    public function index(Request $request)
    {
        try{
            $this->authorize('checkPermission', Service::class);
            $this->limit == $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Service::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'services' => ServiceResource::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ]
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseData());
        }catch(\Exception $e){
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        };
    }

    /**
     * Store a newly created resource in storage.
     */
    // GET /api/dashboard/create
    public function store(ServiceRequest $request)
    {
        try {
            $this->authorize('checkPermission', Service::class);
            $service = Service::create($request->all());
            if (!$service) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $this->authorize('checkPermission', Service::class);
            $service = Service::where('id', $id)->where('deleted', 0)->first();
            empty($service) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'service' => new ServiceResource($service),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
     // GET /api/dashboard/service/update/{id}
    public function update(ServiceRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', Service::class);
            $service = Service::where('id', $id)->where('deleted', 0)->first();
            empty($service) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $serviceUpdated = Service::where('id', $id)->update($request->all());
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
        //  GET /api/dashboard/service/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Service::class);
            $service = Service::where('id', $id)->where('deleted', 0)->first();
            empty($service) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $service->deleted = 1;
            $service->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
