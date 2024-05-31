<?php

namespace App\Http\Controllers\API\Momo;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MomoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function payment(Request $request)
    {
        $userId = Auth::id();
        $bookingId = $request->input('booking_id');
        $booking = Booking::where('id', $bookingId)->where('user_id', $userId)->first();

        if (!$booking) {
            return response()->json(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
        }
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderInfo = "Thanh toán qua MoMo";
        $amount = intval($booking->subtotal);
        $orderId = $partnerCode . Str::uuid();
        $redirectUrl = "https://abc.com"; // Đường dẫn chuyển hướng sau khi thanh toán
        $ipnUrl = route('momo.callback'); // Đường dẫn nhận thông báo sau khi lấy chữ ký IPN từ MoMo
        $extraData = "";
        $requestId =  $orderId;
        $requestType = "payWithATM";
        $requestTime = Carbon::now()->timestamp;
        $expireTime = Carbon::now()->addMinutes(15)->timestamp;

        $rawSignature =
            'accessKey=' . $accessKey .
            '&amount=' . $amount .
            '&extraData=' . $extraData .
            '&ipnUrl=' . $ipnUrl .
            '&orderId=' . $orderId .
            '&orderInfo=' . $orderInfo .
            '&partnerCode=' . $partnerCode .
            '&redirectUrl=' . $redirectUrl .
            '&requestId=' . $requestId .
            '&requestType=' . $requestType;
            '&requestTime=' .  $requestTime;
            '&expireTime=' .  $expireTime;

        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        $requestBody = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'Test',
            'storeId' => 'MomoTestStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'requestType' => $requestType,
            'extraData' => $extraData,
            'signature' => $signature,
        ];

        $response = Http::post('https://test-payment.momo.vn/v2/gateway/api/create', $requestBody);

        return ApiResponse(true, $response->json(), Response::HTTP_OK, messageResponseData());
    }
    public function callback(Request $request)
    {
        return ApiResponse(true, $request->all(), Response::HTTP_OK, messageResponseActionSuccess());
    }

    public function checkStatusTransaction(Request $request)
    {
        $orderId = $request->input('orderId');
        $bookingId = $request->input('booking_id');
        if (!$orderId || !$bookingId) {
            return ApiResponse(false, [], Response::HTTP_BAD_REQUEST, 'Vui lòng kiểm tra lại');
        }

        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $rawSignature = "accessKey={$accessKey}&orderId={$orderId}&partnerCode=MOMOBKUN20180529&requestId={$orderId}";
        $signature = hash_hmac('sha256', $rawSignature, $secretKey);
        $requestBody = [
            'partnerCode' => 'MOMOBKUN20180529',
            'requestId' => $orderId,
            'orderId' => $orderId,
            'signature' => $signature,
            'lang' => 'vi',
        ];

        $response = Http::post('https://test-payment.momo.vn/v2/gateway/api/query', $requestBody);

        $responseData = $response->json();

        if ($responseData['resultCode'] == 0) {
            $booking = Booking::where('id', $bookingId)->first();

            if ($booking) {
                $booking->status = 'Thanh toán thành công';
                $booking->save();
            }
        }
        return ApiResponse(true, $responseData, Response::HTTP_OK, messageResponseData());
    }
}
