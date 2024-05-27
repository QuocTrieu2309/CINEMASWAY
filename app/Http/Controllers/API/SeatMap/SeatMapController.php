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
use App\Models\Seat;

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
            $layout = $seatMap->layout;
            $layoutArr  = explode('|', $layout);
            $seatAll = Seat::where('cinema_screen_id',$seatMap->cinema_screen_id)->get();
            $detail = [];
            $characterArr = ['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'K', 'L', 'M', 'N'];
            foreach ($seatAll as $item) {
                $count = 0;
                foreach ($characterArr as $character) {
                    if ($item['seat_number'][0] == $character) {
                        $detail[$count][] =  [
                            'seat_number' =>  $item['seat_number'],
                            'type' => ''
                        ];
                    }
                    $count++;
                }
            }
            for ($i = 0; $i < count($layoutArr); $i++) {
                $count = countUniqueCharacters($layoutArr[$i]);
                if ($count == 0) {
                    $noSeat[] = [];
                    array_splice($detail, $i, 0, $noSeat);
                } else {
                    for ($j = 0; $j < Str::length($layoutArr[$i]); $j++) {
                        if ($layoutArr[$i][$j] == 'X') {
                            $noSeatNumber = [
                                0 => [
                                    'seat_number' =>  0,
                                ]
                            ];
                            array_splice($detail[$i], $j, 0, $noSeatNumber);
                        }else{
                            $detail[$i][$j]['type'] =$layoutArr[$i][$j];
                        }
                    }
                }
            }

            empty($seatMap) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'seatMap' => new  SeatMapResource($seatMap),
            ];
            return ApiResponse(true, $detail, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    // POST api/dashboard/seat-map/create
    public function store(SeatMapRequest $request)
    {
        try {
            $this->authorize('checkPermission', SeatMap::class);
            $rowCheck = $request->total_row;
            $columnCheck  = $request->total_column;
            $layoutCheck  = $request->layout;
            $row = substr_count($layoutCheck, '|');
            $seatTotalCheck = $request->seat_total;
            $seatTotal = strlen(str_replace(['X', '|'], '', $layoutCheck));
            if ($seatTotal != $seatTotalCheck) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Tổng số lượng ghế không hợp lệ so với sơ đồ ghế.");
            }
            if ($rowCheck != ($row + 1)) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Số hàng không đúng với sơ đồ ghế");
            }
            $layoutArr  = explode('|', $layoutCheck);
            $layoutArrLenght = count($layoutArr);
            for ($i = 0; $i < $layoutArrLenght; $i++) {
                if (Str::length($layoutArr[$i]) != $columnCheck) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Số ghế mỗi hàng không hợp lệ so với sơ đồ ghế");
                }
                if (($i < $layoutArrLenght - 1) && Str::length($layoutArr[$i]) != Str::length($layoutArr[$i + 1])) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Số dãy ghế mỗi hàng không hợp lệ");
                }
                $count = countUniqueCharacters($layoutArr[$i]);
                if ($count > 1) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Mỗi hàng ghế chỉ bao gồm 1 loại ghế và ghế trống");
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

    // POST api/dashboard/seat-map/update/{id}
    public function update(SeatMapRequest $request, $id)
    {
        try {
            $this->authorize('checkPermission', SeatMap::class);
            $seatMap = SeatMap::where('id', $id)->where('deleted', 0)->first();
            empty($seatMap) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $rowCheck = $request->total_row;
            $columnCheck  = $request->total_column;
            $layoutCheck  = $request->layout;
            $row = substr_count($layoutCheck, '|');
            $seatTotalCheck = $request->seat_total;
            $seatTotal = strlen(str_replace(['X', '|'], '', $layoutCheck));
            if ($seatTotal != $seatTotalCheck) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Tổng số lượng ghế không hợp lệ so với sơ đồ ghế.");
            }
            if ($rowCheck != ($row + 1)) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Số hàng không đúng với sơ đồ ghế");
            }
            $layoutArr  = explode('|', $layoutCheck);
            $layoutArrLenght = count($layoutArr);
            for ($i = 0; $i < $layoutArrLenght; $i++) {
                if (Str::length($layoutArr[$i]) != $columnCheck) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Số ghế mỗi hàng không hợp lệ so với sơ đồ ghế");
                }
                if (($i < $layoutArrLenght - 1) && Str::length($layoutArr[$i]) != Str::length($layoutArr[$i + 1])) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Số dãy ghế mỗi hàng không hợp lệ");
                }
                $count = countUniqueCharacters($layoutArr[$i]);
                if ($count > 1) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Mỗi hàng ghế chỉ bao gồm 1 loại ghế và ghế trống");
                }
            }
            $credential = $seatMap->update($request->all());
            if (!$credential) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //DELETE api/dashboard/seat-map/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', SeatMap::class);
            $seatMap = SeatMap::where('id', $id)->where('deleted', 0)->first();
            empty($seatMap) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $seatMap->deleted = 1;
            $seatMap->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
