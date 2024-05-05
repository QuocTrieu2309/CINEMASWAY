<?php

namespace App\Http\Controllers\API\TicketType;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TicketType\TicketTypeRequest;
use App\Http\Resources\API\TicketType\TicketTypeResource;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class TicketTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    //GET api/dashboard/ticket-type
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', TicketType::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = TicketType::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'data' => TicketTypeResource::collection($data),
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
    //POST api/dashboard/ticket-type/create
    public function store(TicketTypeRequest $request)
    {
        try {
            $this->authorize('checkPermission', TicketType::class);
            $ticketType = TicketType::create($request->all());
            if (!$ticketType) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //GET api/dashboard/ticket-type/{id}
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', TicketType::class);
            $ticketType = TicketType::where('id', $id)->where('deleted', 0)->first();
            empty($ticketType) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'ticketType' => new  TicketTypeResource($ticketType),
            ];
            return ApiResponse(true,   $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //PUT api/dashboard/ticket-type/update/{id}
    public function update(TicketTypeRequest $request, $id)
    {
        try {
            $this->authorize('checkPermission', TicketType::class);
            $ticketType = TicketType::where('id', $id)->where('deleted', 0)->first();
            empty($ticketType) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $ticketTypeUpdated = TicketType::where('id', $id)->update($request->all());
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //DELETE api/dashboard/ticket-type/delete/{id}
    public function destroy($id)
    {
        try {
            $this->authorize('checkPermission', TicketType::class);
            $ticketType = TicketType::where('id', $id)->where('deleted', 0)->first();
            empty($ticketType) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $ticketType->deleted = 1;
            $ticketType->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
