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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     *
     * POST api/dashboard/voucher/create
     */
    public function store(VoucherRequest $request)
    {
        try {
            $this->authorize('checkPermission', Voucher::class);
            $inputData = $request->all();
            $vouchersData = isset($inputData['vouchers']) ? $inputData['vouchers'] : [$inputData];
            if (!is_array($vouchersData)) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Invalid data format');
            }
            $createdVouchers = [];
            $errorMessages = [];
            foreach ($vouchersData as $index => $voucherData) {
                $existingCode = Voucher::where('code', $voucherData['code'])->first();
                $existingPin = Voucher::where('pin', $voucherData['pin'])->first();

                if ($existingCode && $existingPin) {
                    $errorMessages[] = 'Voucher số ' . ($index + 1) . ': Mã code: ' . $voucherData['code'] . ' và mã pin: ' . $voucherData['pin'] . ' đã tồn tại.';
                } elseif ($existingCode) {
                    $errorMessages[] = 'Voucher số ' . ($index + 1) . ': Mã code: ' . $voucherData['code'] . ' đã tồn tại.';
                } elseif ($existingPin) {
                    $errorMessages[] = 'Voucher số ' . ($index + 1) . ': Mã pin: ' . $voucherData['pin'] . ' đã tồn tại.';
                } else {
                    $voucher = Voucher::create($voucherData);
                    if (!$voucher) {
                        $errorMessages[] = 'Voucher số ' . ($index + 1) . ': ' . messageResponseActionFailed();
                    } else {
                        $createdVouchers[] = $voucher;
                    }
                }
            }

            if (!empty($errorMessages)) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $errorMessages);
            }

            return ApiResponse(true, $createdVouchers, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
