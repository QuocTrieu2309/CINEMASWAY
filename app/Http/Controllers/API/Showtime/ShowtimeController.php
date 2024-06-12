<?php

namespace App\Http\Controllers\API\Showtime;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Showtime\ShowtimeRequest;
use App\Http\Resources\API\Showtime\ShowtimeResource;
use App\Models\Showtime;
use Carbon\Carbon;
use App\Models\Movie;
use App\Models\Seat;
use App\Models\SeatShowtime;
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
                'showtimes' => ShowtimeResource::collection($data),
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
            $showTime = Carbon::parse($request->show_time);
            $existingShowtimes = Showtime::where('cinema_screen_id', $request->cinema_screen_id)
                ->where('show_date', $request->show_date)
                ->orderBy('show_time')
                ->get();
            $canCreate = true;
            foreach ($existingShowtimes as $existingShowtime) {
                $existingStart = Carbon::parse($existingShowtime->show_time);
                $existingEnd = $existingStart->copy()->addMinutes($existingShowtime->movie->duration);
                if ($existingEnd->diffInMinutes($showTime, false) < 30) {
                    $canCreate = false;
                    break;
                }
            }
            if ($canCreate) {
                $showtime = Showtime::create($request->all());
                if (!$showtime) {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
                $allSeatID = Seat::where('cinema_screen_id', $showtime->cinema_screen_id)->where('status',Seat::STATUS_OCCUPIED)->pluck('id');
                foreach($allSeatID as $seatID){
                   $cridential=  SeatShowtime::query()->create([
                    'showtime_id'=>$showtime->id,
                    'seat_id'=> $seatID,
                    'status' => SeatShowtime::STATUS_AVAILABLE
                   ]);
                   if(!$cridential){
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                   }
                }
                return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
            } else {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Suất chiếu mới phải cách ít nhất 1 giờ so với các suất chiếu khác.');
            }
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
            $showtime = Showtime::find($request->id);
            // $oldCinemaScreenID =  $showtime->cinema_screen_id ;
            if (!$showtime) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseNotFound());
            }
            $showtime->status = $request->status;
            $cridential = $showtime->save();
            if (!$cridential) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            // $showTime = Carbon::parse($request->show_time);
            // $existingShowtimes = Showtime::where('cinema_screen_id', $request->cinema_screen_id)
            //     ->where('show_date', $request->show_date)
            //     ->where('id','!=',$id)
            //     ->orderBy('show_time')
            //     ->get();
            // $canCreate = true;
            // foreach ($existingShowtimes as $existingShowtime) {
            //     $existingStart = Carbon::parse($existingShowtime->show_time);
            //     $existingEnd = $existingStart->copy()->addMinutes($existingShowtime->movie->duration);
            //     if ($existingEnd->diffInMinutes($showTime, false) < 30) {
            //         $canCreate = false;
            //         break;
            //     }
            // }
            // if ($canCreate) {
            //     $check =  $showtime->update($request->all());
            //     if (!$check) {
            //         return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            //     }
            //     if(($showtime->cinema_screen_id) != $oldCinemaScreenID){
            //         $allTicket = SeatShowtime::where('showtime_id',$id)->get();
            //         foreach($allTicket as $ticket){
            //             $ticket->delete();
            //         }
            //     }
            //     $allSeatID = Seat::where('cinema_screen_id', $showtime->cinema_screen_id)->where('status',Seat::STATUS_OCCUPIED)->pluck('id');
            //     foreach($allSeatID as $seatID){
            //        $cridential=  SeatShowtime::query()->create([
            //         'showtime_id'=>$showtime->id,
            //         'seat_id'=> $seatID,
            //         'status' => SeatShowtime::STATUS_AVAILABLE
            //        ]);
            //        if(!$cridential){
            //         return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            //        }
            //     }
            //     return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
            // } else {
            //     return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Suất chiếu mới phải cách ít nhất 1 giờ so với các suất chiếu khác.');
            // }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //DELETE api/dashboard/showtime/delete/{id}
    public function destroy($id)
    {
        try {
            $this->authorize('delete', Showtime::class);
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
