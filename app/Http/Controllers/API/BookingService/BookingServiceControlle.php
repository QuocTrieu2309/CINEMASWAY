<?php

namespace App\Http\Controllers\Api\BookingService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookingService\BookingServiceRequest;
use App\Http\Resources\Api\BookingService\BookingServiceResource;
use App\Models\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class BookingServiceControlle extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/booking-service
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', BookingService::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = BookingService::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'bookingService' => BookingServiceResource::collection($data),
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
    /**
     * Display the specified resource.
     */
    // GET /api/dashboard/booking-service/{id}
    public function show(string $id)
    {
        try {
            $this->authorize('checkPermission', BookingService::class);
            $bookingService = BookingService::where('id', $id)->where('deleted', 0)->first();
            empty($bookingService) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'bookingService' => new BookingServiceResource($bookingService),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    //UPDATE api/dashboard/booking-service/update/{id}

    public function update(BookingServiceRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', BookingService::class);
            $bookingService = BookingService::find($id);
            empty($bookingService) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $bookingService = BookingService::where('id', $id)->where('deleted', 0)->first();
            empty($bookingService) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $bookingServiceUpdate = BookingService::where('id', $id)->update([
                'booking_id' => $request->get('booking_id') ?? $bookingService->booking_id,
                'service_id' => $request->get('service_id') ?? $bookingService->service_id,
                'quantity' => $request->get('quantity') ?? $bookingService->quantity,
                'subtotal' => $request->get('subtotal') ?? $bookingService->subtotal,
            ]);
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    //DELETE api/dashboard/booking-service/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', BookingService::class);
            $bookingService = BookingService::where('deleted', 0)->find($id);
            empty($bookingService) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $bookingService->deleted = 1;
            $bookingService->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
