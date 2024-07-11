<?php

namespace App\Http\Controllers\API\Seat;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Seat\SeatRequest;
use App\Http\Resources\API\Seat\SeatResource;
use App\Models\Seat;
use App\Models\SeatMap;
use App\Models\SeatType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class SeatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * Display a listing of the resource.
     */
    //GET api/dashboard/seat
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Seat::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Seat::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'seats' => SeatResource::collection($data),
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

    /**
     * Store a newly created resource in storage.
     */
    //POST api/dashboard/seat/create
    public function store(SeatRequest $request)
    {
        try {
            $this->authorize('checkPermission', Seat::class);
            $data  = $request->all();
            $seatType = SeatType::find($request->seat_type_id);
            if (!$seatType) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseNotFound());
            }
            $type = $seatType->name;
            $seatAllScreen  = Seat::where('cinema_screen_id', $request->cinema_screen_id)->get();
            $seatMap = SeatMap::where('cinema_screen_id', $request->cinema_screen_id)->first();
            $totalSeatMap = $seatMap->seat_total;
            $totalRow = $seatMap->total_row;
            if (count($seatAllScreen) >= $totalSeatMap) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Số lượng ghế của phòng chiếu đã đầy không thể thêm mới.');
            }
            $characterArr = ['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'K', 'L', 'M', 'N'];
            $seatNumber = $request->seat_number;
            $seatCharacter = $seatNumber[0];
            $characterNumber = substr($seatNumber, 1);
            $indexCharacter = array_search($seatCharacter, $characterArr);
            $layout = $seatMap->layout;
            $layoutArr = explode('|', $layout);
            foreach ($layoutArr as $item) {
                $item = str_replace('X', '', $item);
                if ($item == "") {
                    $totalRow = $totalRow - 1;
                }
            }
            if (($indexCharacter + 1) >  $totalRow) {
                return ApiResponse(false, $totalRow, Response::HTTP_BAD_REQUEST, "Dãy ghế cao nhất của phòng chiếu được bắt đầu bởi kí tự" . " " . $characterArr[$totalRow - 1]);
            } else {
                $layoutRow =  $layoutArr[$indexCharacter];
                $layoutRow = str_replace('X', '', $layoutRow);
                $count = Str::length($layoutRow);
                $seatMapType = $layoutRow[0];
                $checkType = "";
                if ($seatMapType == 'N') {
                    $checkType = 'thường';
                } elseif ($seatMapType == 'V') {
                    $checkType = 'vip';
                } elseif ($seatMapType == 'C') {
                    $checkType = 'đôi';
                }
                if (!str_contains($type,  $checkType)) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Loại ghế không đúng so với loại ghế trong seat map.");
                }
                if ($characterNumber > $count) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Số ghế không được lớn hơn số ghế đang sử dụng trong dãy");
                }
                $seatCharacterAll = Seat::where('seat_number', 'LIKE', $seatCharacter . '%')
                    ->where('cinema_screen_id', $request->cinema_screen_id)
                    ->get();
                $countSeat = count($seatCharacterAll);
                if ($countSeat >= $count) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Hàng ghế này đã đầy không thể tạo thêm");
                } else {
                    $cridential = Seat::query()->create($data);
                    if (!$cridential) {
                        return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                    }
                }
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    // GET /api/dashboard/seat/{id}
    public function show(string $id)
    {
        try {
            $this->authorize('checkPermission', Seat::class);
            $seat = Seat::where('id', $id)->where('deleted', 0)->first();
            empty($seat) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'Seat' => new SeatResource($seat),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    //UPDATE api/dashboard/seat/update/{id}
    public function update(SeatRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', Seat::class);
            $seat = Seat::where('id', $id)->where('deleted', 0)->first();
            $seat->status = $request->status;
            $cridential = $seat->save();
            if (!$cridential) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            // empty($seat) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            // $data  = $request->all();
            // $seatAllScreen  = Seat::where('cinema_screen_id',$seat->cinema_screen_id)->where('id','!=',$id)->get();
            // $seatMap = SeatMap::where('cinema_screen_id',$request->cinema_screen_id)->first();
            // $totalSeatMap = $seatMap->seat_total;
            // $totalRow = $seatMap->total_row;
            // if(count($seatAllScreen)>= $totalSeatMap ){
            //     return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Số lượng ghế của phòng chiếu đã đầy không thể thêm mới.');
            // }
            // $characterArr = ['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'K', 'L', 'M', 'N'];
            // $seatNumber = $request->seat_number;
            // $seatCharacter = $seatNumber[0];
            // $characterNumber = substr($seatNumber,1);
            // $indexCharacter = array_search($seatCharacter,$characterArr);
            // $layout = $seatMap->layout;
            // $layoutArr = explode('|', $layout);
            // foreach( $layoutArr as $item){
            //     $item = str_replace('X', '', $item);
            //     if($item == ""){
            //        $totalRow = $totalRow -1;
            //     }
            // }
            // if(($indexCharacter+1) >  $totalRow){
            //     return ApiResponse(false, $totalRow, Response::HTTP_BAD_REQUEST, "Dãy ghế cao nhất của phòng chiếu được bắt đầu bởi kí tự". " ".$characterArr[$totalRow-1]);
            // }else{
            //     $layoutRow =  $layoutArr[$indexCharacter+1];
            //     $layoutRow = str_replace('X', '',$layoutRow);
            //     $count = Str::length($layoutRow);
            //     if($characterNumber > $count ){
            //         return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Số ghế không được lớn hơn số ghế đang sử dụng trong dãy");
            //     }
            //     $seatCharacterAll = Seat::where('seat_number', 'LIKE', $seatCharacter . '%')
            //                         ->where('cinema_screen_id', $request->cinema_screen_id)
            //                         ->where('id','!=',$id)
            //                         ->get();
            //     $countSeat = count($seatCharacterAll);
            //     if($countSeat >= $count){
            //         return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Hàng ghế này đã đầy không thể tạo thêm");
            //     }else{
            //       $cridential = $seat->update($data);
            //       if(!$cridential){
            //         return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            //       }
            //     }
            // }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    //DELETE api/dashboard/seat/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Seat::class);
            $seat = Seat::where('id', $id)->where('deleted', 0)->first();
            empty($seat) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            // $seat->deleted = 1;
            // $seat->save();
            $hasRelatedRecords = $seat->seatShowtime()->exists() ||
                $seat->cinemaScreen()->exists() ||
                $seat->seatType()->exists() ||
                $seat->ticket()->exists();
            if ($hasRelatedRecords) {
                $seat->deleted = 1;
                $seat->save();
            } else {
                $seat->delete();
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
