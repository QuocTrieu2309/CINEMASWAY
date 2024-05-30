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
    public function createPayment(Request $request)
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
        $orderId = time();
        $redirectUrl = "https://fpt.edu.vn"; // Đường dẫn chuyển hướng sau khi thanh toán
        $ipnUrl = "https://abc.com"; // Đường dẫn nhận thông báo sau khi lấy chữ ký IPN từ MoMo
        $extraData = "";
        $requestId = time();
        $requestType = "payWithATM";

        // Tạo chữ ký hash
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "Test",
            'storeId' => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        $response = Http::post($endpoint, $data);
        if ($response->successful()) {
            $paymentUrl = $response['payUrl'];
            $booking = Booking::findOrFail($bookingId);
            if ($booking->status === 'đã thanh toán' || $booking->status === 'đã hủy') {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Đơn hàng đã được xử lý hoặc đã hủy');
            } else {
                $booking->update(['status' => 'đã thanh toán']);
                return ApiResponse(true, $paymentUrl, Response::HTTP_OK, messageResponseActionSuccess());
            }
        } else {
            return ApiResponse(false, Null, Response::HTTP_BAD_REQUEST, 'yêu cầu thanh toán thất bại'());
        }
    }
}
