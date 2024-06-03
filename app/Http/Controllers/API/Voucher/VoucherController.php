<?php

namespace App\Http\Controllers\API\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Voucher\VoucherRequest;
use App\Http\Resources\API\Voucher\VoucherResource;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class VoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get list voucher
     * @param Request $request
     * @return mixed
     *
     * GET api/dashboard/vouher
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Voucher::class);
            $this->limit == $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Voucher::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'transactions' => VoucherResource::collection($data),
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
     * Create new voucher
     *
     * @param VoucherRequest $request
     * @return mixed
     *
     * POST api/dashboard/voucher/create
     */
    public function store(VoucherRequest $request)
    {
        try {
            $this->authorize('checkPermission', Voucher::class);
            $data = $request->all();
            $cridential = Voucher::query()->create($data);
            if(!$cridential){
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Show information about the voucher
     * @param string $id
     * @return mixed
     *
     * Get api/dashboard/voucher/{id}
     */
    public function show(string $id)
    {
        try {
            $this->authorize('checkPermission', Voucher::class);
            $voucher = Voucher::where('id', $id)->where('deleted', 0)->first();
            empty($voucher) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'voucher' => new  VoucherResource($voucher),
            ];
            return ApiResponse(true,   $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Updates a Voucher
     * @param VoucherRequest $request
     * @param string $id
     * @return mixed
     *
     * Put api/dashboard/voucher/update/{id}
     */
    public function update(VoucherRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', Voucher::class);
            $voucher = Voucher::where('id', $id)->where('deleted', 0)->first();
            empty($voucher) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $voucherUpdate = Voucher::where('id', $id)->update([
                'code' => $request->get('code') ?? $voucher->code,
                'type' => $request->get('type') ?? $voucher->type,
                'value' => $request->get('value') ?? $voucher->value,
                'start_date' => $request->get('start_date') ?? $voucher->start_date,
                'end_date' => $request->get('end_date') ?? $voucher->end_date,
                'status' => $request->get('status') ?? $voucher->status,
                'description' => $request->get('description') ?? $voucher->description,
            ]);
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Delete voucher
     * @param string $id
     * @return mixed
     *
     * Delete api/dashboard/voucher/delete/{id}
     */
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Voucher::class);
            $voucher = Voucher::where('id', $id)->where('deleted', 0)->first();
            empty($voucher) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $voucher->deleted = 1;
            $voucher->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
