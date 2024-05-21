<?php

namespace App\Http\Controllers\API\SeatMap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeatMap;
use App\Http\Resources\API\SeatMap\SeatMapResource;
use App\Http\Requests\API\SeatMap\SeatMapRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class SeatMapController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:sanctum');
    // }

    //GET api/dashboard/seat-map
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', SeatMap::class);
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

    //GET api/dashboard/seat-map/{id}
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', SeatMap::class);
            $seatMap = SeatMap::where('id', $id)->where('deleted', 0)->first();
            empty($seatMap) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'seatMap' => new  SeatMapResource($seatMap),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    // POST api/dashboard/seat-type/create
    public function store(SeatMapRequest $request)
    {
        try {
            // $this->authorize('checkPermission', SeatMap::class);
            $rowCheck = $request->total_row;
            $columnCheck  = $request->total_column;
            $layoutCheck  = $request->layout;
            $row = substr_count($layoutCheck,'|');
            if($rowCheck !=($row+1) ){
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,"Số hàng không đúng với sơ đồ ghế");
            }
            $layoutArr  = explode('|', $layoutCheck);
            $layoutArrLenght = count($layoutArr);
            for($i = 0; $i < $layoutArrLenght; $i++){
                if(Str::length($layoutArr[$i]) != $columnCheck){
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,"Số ghế mỗi hàng không hợp lệ so với sơ đồ ghế");
                }
                if(($i<$layoutArrLenght-1) && Str::length($layoutArr[$i])!= Str::length($layoutArr[$i+1]) ){
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,"Số dãy ghế mỗi hàng không hợp lệ");
                }
                $count = countUniqueCharacters($layoutArr[$i]);
                if($count >1 ){
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,"Mỗi hàng ghế chỉ bao gồm 1 loại ghế và ghế trống");
                }
            }
            $seatMap = SeatMap::create($request->all());
            if (!$seatMap) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

}
