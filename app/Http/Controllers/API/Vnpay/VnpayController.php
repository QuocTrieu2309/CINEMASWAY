<?php

namespace App\Http\Controllers\API\Vnpay;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class VnpayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //POST api/pay/vnpay (truyền booking_id, price)
    public function index(Request $request)
    {
        try {
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = "http://127.0.0.1:8000";
            $vnp_TmnCode = "5N23P3P2";
            $vnp_HashSecret = "IXXRTPQNFDPFJHDKXSUJZOJURZQLMJIK";
            $vnp_TxnRef = $request->booking_id; // $request->booking_id
            $vnp_OrderInfo = "Thanh toán vé xem phim";
            $vnp_OrderType = "billpayment";
            $vnp_Amount = $request->price * 100; //$request->price
            $vnp_Locale = "vn";
            $vnp_BankCode = "NCB";
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            );
            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }
            if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
                $inputData['vnp_Bill_State'] = $vnp_Bill_State;
            }
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }
            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }
            return redirect($vnp_Url);
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //POST api/pay/vnpay/send (key: vnp_TransactionStatus, vnp_TxnRef, vnp_Amount)
    public function send(Request $request)
    {
        try {
            if ($request->vnp_TransactionStatus == 00) {
                $booking = Booking::where('id', $request->vnp_TxnRef)->where('deleted', 0)->first();
                empty($booking) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
                $bookingUpdate = Booking::where('id', $request->vnp_TxnRef)->update([
                    'status' => "Thanh toán thành công"
                ]);
                if ($bookingUpdate) {
                    $rl = Transaction::create([
                        "booking_id" => $request->vnp_TxnRef,
                        "subtotal" => $request->vnp_Amount,
                        "payment_method" => "Vnpay",
                        "status" => Transaction::STATUS_SUCCESS,
                    ]);
                    if ($rl) {
                        return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
                    } else {
                        return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                    }
                } else {
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
            } else {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Đã hủy thanh toán");
            }
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
