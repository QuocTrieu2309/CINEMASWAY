<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Booking\BookingResource;
use App\Http\Resources\API\BookingService\BookingServiceResource;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class ListBookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // GET api/client/booking
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), 'desc');
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), 'created_at');
            $data = Booking::where('deleted', 0)
                ->where('user_id', $user->id)
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);
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
    // GET api/client/booking/{id}
    public function show($id)
    {
        try {
            $user = auth()->user();
            $booking = Booking::where('id', $id)->where('user_id', $user->id)->where('deleted', 0)->first();
            empty($booking) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data['booking'] = new BookingResource($booking);
            $bookingServices = BookingService::where('booking_id', $booking->id)->get();
            $total = 0;
            $data['services'] = BookingServiceResource::collection($bookingServices);
            foreach ($bookingServices as $bookingService) {
                $total += $bookingService->subtotal;
            }
            $tickets = Ticket::where('booking_id', $booking->id)->get();
            if (!$tickets) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, messageResponseActionFailed());
            }
            $quantity = count($tickets);
            $ticketSubtotal = $booking->subtotal - $total;
            $data['ticket'] = [
                'quantity' => $quantity,
                'subtotal' => $ticketSubtotal,
            ];
            $seats = $tickets->map(function ($ticket) {
                return [
                    'seat_number' => $ticket->seat->seat_number,
                    'seat_type' => $ticket->seat->seatType->name,
                ];
            });
            $data['seats'] = $seats;
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
