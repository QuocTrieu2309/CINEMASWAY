<?php

namespace App\Http\Controllers\API\Booking;

use App\Http\Controllers\Controller;
// use App\Http\Requests\API\Booking\BookingRequest;
use App\Http\Resources\API\Booking\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    // //GET api/dashboard/booking
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Booking::class);
            $this->limit == $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Booking::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'bookings' => BookingResource::collection($data),
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
    public function store(Request $request)
    {
        //
    }
    //GET api/dashboard/booking/{id}
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', Booking::class);
            $booking = Booking::where('id', $id)->where('deleted', 0)->first();
            empty($booking) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'booking' => new  BookingResource($booking),
            ];
            return ApiResponse(true,   $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //GET api/dashboard/booking/update/{id}
    // public function update(BookingRequest $request, $id)
    // {
    //     try {
    //         $this->authorize('checkPermission', Booking::class);
    //         $booking = Booking::where('id', $id)->where('deleted', 0)->first();
    //         empty($booking) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
    //         $bookingUpdated = Booking::where('id', $id)->update($request->all());
    //         return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
    //     } catch (\Exception $e) {
    //         return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
    //     }
    // }
    //GET api/dashboard/booking/delete/{id}
    public function destroy($id)
    {
        try {
            $this->authorize('delete', Booking::class);
            $booking = Booking::where('id', $id)->where('deleted', 0)->first();
            empty($booking) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $booking->deleted = 1;
            $booking->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
