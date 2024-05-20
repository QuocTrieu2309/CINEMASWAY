<?php

namespace App\Http\Controllers\API\SeatMap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeatMap;
use App\Http\Resources\API\SeatMap\SeatMapResource;
use App\Http\Requests\API\SeatMap\SeatMapRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class SeatMapController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/seat-map
    public function index(Request $request)
    {
        try {
            // $this->authorize('checkPermission', SeatMap::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = SeatMap::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'seatMaps' => SeatMapResource::collection($data),
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
}
