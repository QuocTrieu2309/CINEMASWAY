<?php


namespace App\Http\Controllers\API\Momo;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Client\ClientRequest;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\SeatShowtime;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\Transaction;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Milon\Barcode\DNS1D;

class MomoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // tạo link thanh toán
    public function payment(ClientRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = auth('sanctum')->user();
            if (!$user) {
                return ApiResponse(false, null, Response::HTTP_UNAUTHORIZED, 'Vui long đăng nhập');
            }
            $barcode = new DNS1D();
            $barcodeString = $barcode->getBarcodePNG(uniqid(), 'C128', 3, 33);
            $tempBarcodePath = tempnam(sys_get_temp_dir(), 'barcode') . '.png';
            file_put_contents($tempBarcodePath, base64_decode($barcodeString));
            $uploadedFileUrl = Cloudinary::uploadFile($tempBarcodePath, [
                'folder' => 'Booking'
            ])->getSecurePath();
            unlink($tempBarcodePath);

            $totalSubtotal = 0;
            // Tính tổng tiền vé
            $totalSubtotal += $request->subtotal;
            $booking = Booking::create([
                'user_id' => $user->id,
                'showtime_id' => $request->showtime_id,
                'code' => $uploadedFileUrl,
                'quantity' => count($request->seats),
                'subtotal' => $request->subtotal,
                'status' => Booking::STATUS_UNPAID,
            ]);

            if (!$booking) {
                DB::rollBack();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }

            foreach ($request->seats as $seatId) {
                $isReserved = SeatShowtime::where('seat_id', $seatId)
                    ->where('status', SeatShowtime::STATUS_RESERVED)
                    ->exists();
                if ($isReserved) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'ghế đã được đặt vui lòng chọn ghế khác');
                }
                Ticket::create([
                    'booking_id' => $booking->id,
                    'seat_id' => $seatId,
                ]);
            }
            foreach ($request->services as $service) {
                $serviceModel = Service::findOrFail($service['service_id']);

                if ($serviceModel->quantity < $service['quantity']) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Dịch vụ ' . $serviceModel->name . ' không đủ số lượng để đáp ứng. Vui lòng giảm số lượng dịch vụ!');
                }
                $totalSubtotal += $service['subtotal'];
                $bookingService = BookingService::create([
                    'booking_id' => $booking->id,
                    'service_id' => $service['service_id'],
                    'quantity' => $service['quantity'],
                    'subtotal' => $service['subtotal'],
                ]);
                if (!$bookingService) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
                $serviceModel->decrement('quantity', $service['quantity']);
            }
            // Cập nhật tổng tiền booking
            $booking->subtotal = $totalSubtotal;
            $booking->save();
            $accessKey = 'F8BBA842ECF85';
            $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
            $partnerCode = 'MOMO';
            $redirectUrl = $request->url. '/MOMO?booking_id=' . $booking->id;
            $ipnUrl = route('momo.callback');
            $orderInfo = 'pay with MoMo';
            $requestType = 'payWithATM';
            $orderExpireTime = 5;
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
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://test-payment.momo.vn/v2/gateway/api/create', [
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
                'orderExpireTime' => $orderExpireTime,
                'signature' => $signature,
            ]);

            if ($response->successful()) {
                DB::commit();
                return ApiResponse(true, [
                    'booking_id' => $booking->id,
                    'payment_link' => $response->json(), // Example response data
                ], Response::HTTP_OK, messageResponseData());
            } else {
                DB::rollBack();
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Tạo link thanh toán thât bại MoMo payment');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }



    public function callback(Request $request)
    {
        return ApiResponse(true, $request->all(), Response::HTTP_OK, messageResponseActionSuccess());
    }

    // phản hồi xác nhận thanh toán thành công momo
    public function checkStatusTransaction(Request $request)
    {
        try {
            $orderId = $request->input('orderId');
            $bookingId = $request->input('booking_id');
            $booking = Booking::find($bookingId);
            if (!$booking) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có booking nào');
            }
            if ($booking->status == 'Payment successful') {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Booking đã được thanh toán thành công');
            }
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
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://test-payment.momo.vn/v2/gateway/api/query', $requestBody);

            $responseData = $response->json();

            if ($responseData['resultCode'] == 0) {
                $booking->status = 'Payment successful';
                $booking->save();
                $seats = Ticket::where('booking_id', $bookingId)->pluck('seat_id')->toArray();

                foreach ($seats as $seatId) {
                    $seatShowtime = SeatShowtime::where('seat_id', $seatId)
                        ->where('showtime_id', $booking->showtime_id)
                        ->first();
                    if ($seatShowtime) {
                        $seatShowtime->user_id = $booking->user_id;
                        $seatShowtime->status = SeatShowtime::STATUS_RESERVED;
                        $seatShowtime->save();
                    }
                }

                $transaction = new Transaction();
                $transaction->booking_id = $bookingId;
                $transaction->subtotal = $booking->subtotal;
                $transaction->payment_method = 'Momo';
                $transaction->status = 'Đã thanh toán';
                $transaction->save();
            } elseif ($responseData['resultCode'] == 1006) {
                DB::beginTransaction();
                try {
                    Ticket::where('booking_id', $bookingId)->delete();
                    $bookingServices = BookingService::where('booking_id', $bookingId)->get();
                    foreach ($bookingServices as $bookingService) {
                        $bookingService->delete();
                    }
                    Booking::where('id', $bookingId)->delete();
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
                }
            }
            return ApiResponse(true, $responseData, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
