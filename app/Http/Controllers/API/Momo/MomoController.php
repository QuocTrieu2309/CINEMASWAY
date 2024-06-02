<?php

namespace App\Http\Controllers\API\Momo;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
        $accessKey = 'F8BBA842ECF85';
        $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
        $partnerCode = 'MOMO';
        $redirectUrl = 'https://abc.com';
        $ipnUrl = route('momo.callback');
        $orderInfo = 'pay with MoMo';
        $requestType = 'payWithATM';
        $extraData = '';
        $orderGroupId = '';
        $autoCapture = true;
        $lang = 'vi';

        $amount = intval($booking->subtotal);
        $orderId = $partnerCode . time();
        $requestId = $orderId;

        $rawSignature = sprintf(
            'accessKey=%s&amount=%s&extraData=%s&ipnUrl=%s&orderId=%s&orderInfo=%s&partnerCode=%s&redirectUrl=%s&requestId=%s&requestType=%s',
            $accessKey,
            $amount,
            $extraData,
            $ipnUrl,
            $orderId,
            $orderInfo,
            $partnerCode,
            $redirectUrl,
            $requestId,
            $requestType
        );

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
            'lang' => $lang,
            'requestType' => $requestType,
            'autoCapture' => $autoCapture,
            'extraData' => $extraData,
            'orderGroupId' => $orderGroupId,
            'signature' => $signature,
        ];
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://test-payment.momo.vn/v2/gateway/api/create', $requestBody);
            return ApiResponse(true, $response->json(), Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    public function callback(Request $request)
    {
        return ApiResponse(true, $request->all(), Response::HTTP_OK, messageResponseActionSuccess());
    }

    public function checkStatusTransaction(Request $request)
    {
        $userId = Auth::id();
        $bookingId = $request->input('booking_id');
        $booking = Booking::where('id', $bookingId)->where('user_id', $userId)->first();
        if (!$booking) {
            return response()->json(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
        }
        $orderId = $request->input('orderId');
        if (!$orderId || !$bookingId) {
            return ApiResponse(false, [], Response::HTTP_BAD_REQUEST, 'Vui lòng kiểm tra lại');
        }

        $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
        $accessKey = 'F8BBA842ECF85';
        $partnerCode = 'MOMO';

        $rawSignature = sprintf(
            'accessKey=%s&orderId=%s&partnerCode=%s&requestId=%s',
            $accessKey,
            $orderId,
            $partnerCode,
            $orderId
        );

        $signature = hash_hmac('sha256', $rawSignature, $secretKey);
        $requestBody = [
            'partnerCode' => $partnerCode,
            'requestId' => $orderId,
            'orderId' => $orderId,
            'signature' => $signature,
            'lang' => 'vi',
        ];
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://test-payment.momo.vn/v2/gateway/api/query', $requestBody);

            $responseData = $response->json();

            if ($responseData['resultCode'] == 0) {
                $booking = Booking::where('id', $bookingId)->first();
                if ($booking) {
                    $booking->status = 'Thanh toán thành công';
                    $booking->save();
                }
            }
            return ApiResponse(true, $responseData, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
