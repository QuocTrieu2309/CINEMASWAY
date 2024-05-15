<?php

namespace App\Http\Controllers\API\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Http\Resources\API\Ticket\TicketResource;
use App\Http\Requests\API\Ticket\TicketRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * GET api/dashboard/ticket
     * @param Request $request
     * @return ApiResponse
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission',Ticket::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Ticket::where('deleted',0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'tickets' => TicketResource::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];

            return ApiResponse(true,$result, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * GET api/dashboard/ticket/{id}
     * @param $id
     * @return ApiResponse
     */
    public function show($id)
    {
        try {
            $this->authorize('checkPermission',Ticket::class);
            $ticket = Ticket::where('id', $id)->where('deleted', 0)->first();
            empty($ticket) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'ticket' => new  TicketResource($ticket),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * POST api/dashboard/ticket/create
     * @param TicketRequest $request
     * @return ApiResponse
     */
    public function store(TicketRequest $request)
    {
        try {
            $this->authorize('checkPermission', Ticket::class);
            $seatType = Ticket::create($request->all());
            if (!$seatType) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * UPDATE api/dashboard/ticket/update/{id}
     * @param TicketRequest $request
     * @param $id
     * @return ApiResponse
     */
    public function update(TicketRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission',Ticket::class);
            $ticket = Ticket::where('id', $id)->where('deleted', 0)->first();
            empty($ticket) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $ticketUpdate = Ticket::where('id', $id)->update([
                'booking_id' => $request->get('booking_id') ?? $ticket->booking_id,
                'showtime_id' => $request->get('showtime_id') ?? $ticket->showtime_id,
                'seat_id' => $request->get('seat_id') ?? $ticket->seat_id,
                'code' => $request->get('code') ?? $ticket->code,
                'status' => $request->get('status') ?? $ticket->status,
            ]);

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     *
     * @param string $id
     * @return ApiResponse|
     */
    //DELETE api/dashboard/ticket/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Ticket::class);
            $ticket = Ticket::where('id', $id)->where('deleted', 0)->first();
            empty($ticket) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $ticket->deleted = 1;
            $ticket->save();

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
