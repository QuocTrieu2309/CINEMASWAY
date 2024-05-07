<?php

namespace App\Http\Controllers\API\Showtime;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Showtime\ShowtimeRequest;
use App\Http\Resources\API\Showtime\ShowtimeResource;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class ShowtimeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    //GET api/dashboard/showtime
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Showtime::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Showtime::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'data' => ShowtimeResource::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    // POST api/dashboard/showtime/create
    public function store(ShowtimeRequest $request)
    {
        try {
            $this->authorize('checkPermission', Showtime::class);
            $showtime = Showtime::create($request->all());
            if (!$showtime) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //GET api/dashboard/showtime/{id}
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', Showtime::class);
            $showtime = Showtime::where('id', $id)->where('deleted', 0)->first();
            empty($showtime) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'showtime' => new  ShowtimeResource($showtime),
            ];
            return ApiResponse(true,   $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //PUT api/dashboard/showtime/update/{id}
    public function update(ShowtimeRequest $request, $id)
    {
        try {
            $this->authorize('checkPermission', Showtime::class);
            $showtime = Showtime::where('id', $id)->where('deleted', 0)->first();
            empty($showtime) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $showtimeUpdated = Showtime::where('id', $id)->update($request->all());
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //DELETE api/dashboard/showtime/delete/{id}
    public function destroy($id)
    {
        try {
            $this->authorize('checkPermission', Showtime::class);
            $showtime = Showtime::where('id', $id)->where('deleted', 0)->first();
            empty($showtime) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $showtime->deleted = 1;
            $showtime->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
