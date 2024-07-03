<?php

namespace App\Http\Controllers\API\Vnpay;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\SeatShowtime;
use App\Models\Showtime;
use App\Models\Ticket;
use App\Models\Transaction;
use Cloudinary\Api\Metadata\Validators\StringLength;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;

class VnpayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //POST api/pay/vnpay
    public function index(Request $request)
    {
        DB::beginTransaction();
        try {
            $barcode = new DNS1D();
            $barcodeString = $barcode->getBarcodePNG(uniqid(), 'C128', 3, 33);
            $tempBarcodePath = tempnam(sys_get_temp_dir(), 'barcode') . '.png';
            file_put_contents($tempBarcodePath, base64_decode($barcodeString));
            $uploadedFileUrl = Cloudinary::uploadFile($tempBarcodePath, [
                'folder' => 'Booking'
            ])->getSecurePath();
            unlink($tempBarcodePath);
            $booking = Booking::create([
                'user_id' => $request->user_id,
                'showtime_id' => $request->Showtimes_id,
                'code' => $uploadedFileUrl, //rand(1, 9999)
                'quantity' => count($request->seats),
                'subtotal' => $request->subtotal,
                'status' => Booking::STATUS_UNPAID,
            ]);
            if (!$booking) {
                DB::rollBack();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            foreach ($request->seats as $seatId) {
                $cridential = Ticket::query()->create([
                    'booking_id' => $booking->id,
                    'seat_id' => $seatId
                ]);
                $seatShotime = SeatShowtime::where('seat_id', $seatId)->update([
                    'status' => SeatShowtime::STATUS_HELD
                ]);
                if (!$cridential || !$seatShotime) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
            }
            foreach ($request->services as $service) {
                $bookingService = BookingService::query()->create([
                    'booking_id' => $booking->id,
                    'service_id' => $service['service_id'],
                    'quantity' => $service['quantity'],
                    'subtotal' => $service['subtotal']
                ]);
                if (!$bookingService) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
            }
            $transaction = Transaction::query()->create([
                'booking_id' => $booking->id,
                'subtotal' => $booking->subtotal,
                'payment_method' => "Thanh toán Vnpay",
                'status' => Transaction::STATUS_FAIL,
            ]);
            if (!$transaction) {
                DB::rollBack();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = $request->url;
            $vnp_TmnCode = "5N23P3P2";
            $vnp_HashSecret = "IXXRTPQNFDPFJHDKXSUJZOJURZQLMJIK";
            $vnp_TxnRef = $booking->id; // $request->booking_id
            $vnp_OrderInfo = "Thanh toán vé xem phim";
            $vnp_OrderType = "billpayment";
            $vnp_Amount = $booking->subtotal * 100; //$request->price
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
            DB::commit();
            $data = [
                "url" => $vnp_Url,
                "booking_id" => $booking->id,
                "transaction_id" => $transaction->id,
                "seats" => $request->seats,
                "services" => $request->services
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //POST api/pay/vnpay/send (key: vnp_TransactionStatus, booking_id, transaction_id , seats, services) 
    // * vnp_TransactionStatus: của vnpay trả về
    public function send(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->vnp_TransactionStatus == 00) {
                $booking = Booking::where('id', $request->booking_id)->update([
                    'status' => Booking::STATUS_PAID
                ]);
                if (!$booking) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
                $transactionUpdate = Transaction::where('id', $request->transaction_id)->update([
                    "status" => Transaction::STATUS_SUCCESS,
                ]);
                if (!$transactionUpdate) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
                foreach ($request->seats as $seatId) {
                    $seatShotime = SeatShowtime::where('seat_id', $seatId)->update([
                        'status' => SeatShowtime::STATUS_RESERVED
                    ]);
                    if (!$seatShotime) {
                        DB::rollBack();
                        return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                    }
                }
                DB::commit();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            } else {
                $booking = Booking::where('id', $request->booking_id)->update([
                    'deleted' => 1
                ]);
                if (!$booking) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
                $transactionUpdate = Transaction::where('id', $request->transaction_id)->update([
                    "deleted" => 1
                ]);
                if (!$transactionUpdate) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
                foreach ($request->seats as $seatId) {
                    $cridential = Ticket::where('seat_id', $seatId)->update([
                        'deleted' => 1
                    ]);
                    $seatShotime = SeatShowtime::where('seat_id', $seatId)->update([
                        'status' => SeatShowtime::STATUS_SELECTED
                    ]);
                    if (!$cridential || !$seatShotime) {
                        DB::rollBack();
                        return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                    }
                }
                foreach ($request->services as $service) {
                    $bookingService = BookingService::where('service_id', $service['service_id'])->update([
                        'deleted' => 1
                    ]);
                    if (!$bookingService) {
                        DB::rollBack();
                        return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                    }
                }
                DB::commit();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, "Đã hủy thanh toán");
            }
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
