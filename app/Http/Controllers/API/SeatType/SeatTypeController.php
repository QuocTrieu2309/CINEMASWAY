<?php

namespace App\Http\Controllers\API\SeatType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeatType;
use App\Http\Resources\API\SeatType\SeatTypeResource;
use App\Http\Requests\API\SeatType\SeatTypeRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SeatTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * GET api/dashboard/seat-type
     * @param Request $request
     * @return ApiResponse
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', SeatType::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = SeatType::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'seatTypes' => SeatTypeResource::collection($data),
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
     * GET api/dashboard/seat-type/{id}
     * @param $id
     * @return ApiResponse
     */
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', SeatType::class);
            $seatType = SeatType::where('id', $id)->where('deleted', 0)->first();
            empty($seatType) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'seatType' => new  SeatTypeResource($seatType),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * POST api/dashboard/seat-type/create
     * @param SeatType $seatType
     * @return ApiResponse
     */
    public function store(SeatTypeRequest $request)
    {
        try {
            $this->authorize('checkPermission', SeatType::class);
            $seatType = SeatType::create($request->all());
            if (!$seatType) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * UPDATE api/dashboard/seat-type/update/{id}
     * @param SeatTypeRequest $request
     * @param $id
     * @return ApiResponse
     */
    public function update(SeatTypeRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', SeatType::class);
            $seatType = SeatType::where('id', $id)->where('deleted', 0)->first();
            empty($seatType) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $seatTypeUpdate = SeatType::where('id', $id)->update([
                'name' => $request->get('name') ?? $seatType->name
            ]);

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    //DELETE api/dashboard/seat-type/delete/{id}
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $this->authorize('delete', SeatType::class);
            $seatType = SeatType::where('id', $id)->where('deleted', 0)->first();
            empty($seatType) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $hasRelatedRecords = $seatType->seats()->exists() || $seatType->screen()->exists();
            if ($hasRelatedRecords) {
                $seatType->deleted = 1;
                $seatType->save();
            } else {
                $seatType->delete();
            }
            DB::commit();

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
