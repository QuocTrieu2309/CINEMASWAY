<?php

namespace App\Http\Controllers\API\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Transaction\TransactionRequest;
use App\Http\Resources\API\Transaction\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class TransactionController extends Controller
{
    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all transactions
     * @param Request $request
     * @return mixed
     * GET api/dashboard/transaction
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Transaction::class);
            $this->limit == $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Transaction::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'transactions' => TransactionResource::collection($data),
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
     * Get transaction by id
     * @param $id
     * @return mixed
     *
     * GET api/dashboard/transaction/{id}
     */
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', Transaction::class);
            $transaction = Transaction::where('id', $id)->where('deleted', 0)->first();
            empty($transaction) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'transaction' => new  TransactionResource($transaction),
            ];
            return ApiResponse(true,   $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Update status in transaction by id
     * @param
     * @param string $id
     * @return mixed
     *
     * PUT api/dashboard/transaction/update/{id}
     */
    public function update(TransactionRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', Transaction::class);
            $transaction = Transaction::where('id', $id)->where('deleted', 0)->first();
            empty($transaction) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $transactionUpdate = Transaction::where('id', $id)->update([
                'status' => $request->get('status') ?? $transaction->status,
            ]);
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Delete a transaction by id
     * @param string $id
     * @return mixed
     *
     * DELETE api/dashboard/transaction/delete/{id}
     */
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Transaction::class);
            $transaction = Transaction::where('id', $id)->where('deleted', 0)->first();
            empty($transaction) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $transaction->deleted = 1;
            $transaction->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
