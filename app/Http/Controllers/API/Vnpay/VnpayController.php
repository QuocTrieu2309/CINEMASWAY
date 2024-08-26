<?php

namespace App\Http\Controllers\API\Vnpay;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Seat;
use App\Models\SeatShowtime;
use App\Models\Service;
use App\Models\Showtime;
use App\Models\Ticket;
use App\Models\Transaction;
use Carbon\Carbon;
use Cloudinary\Api\Metadata\Validators\StringLength;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
            do {
                $ticketCode = random_int(1000000000000, 9999999999999);
            } while (Booking::where('ticket_code', $ticketCode)->exists());
            $totalSubtotal = 0;
            // Tính tổng tiền vé
            $totalSubtotal += $request->subtotal;
            $booking = Booking::create([
                'user_id' => $user->id,
                'showtime_id' => $request->showtime_id,
                'code' => $uploadedFileUrl,
                'ticket_code' => $ticketCode,
                'quantity' => count($request->seats),
                'subtotal' => $request->subtotal,
                'status' => Booking::STATUS_UNPAID,
            ]);
            if (!$booking) {
                DB::rollBack();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            foreach ($request->seats as $seatId) {
                // $isReserved = SeatShowtime::where('seat_id', $seatId)
                //     ->where('status', SeatShowtime::STATUS_RESERVED)
                //     ->exists();
                // if ($isReserved) {
                //     DB::rollBack();
                //     return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'ghế đã được đặt vui lòng chọn ghế khác');
                // }
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
                // $totalSubtotal += $service['subtotal'];
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
            // $booking->subtotal = $totalSubtotal;
            $booking->save();
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = $request->url . "/VNPAY";
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
            $data = [
                "url" => $vnp_Url
            ];
            DB::commit();
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //POST api/pay/vnpay/send (key: vnp_TransactionStatus, vnp_TxnRef)
    public function send(Request $request)
    {
        DB::beginTransaction();
        try {
            $booking = Booking::find($request->vnp_TxnRef);
            if (!$booking) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có booking nào');
            }
            if ($booking->status == 'Payment successful') {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Booking đã được thanh toán thành công');
            }
            if (!$request->vnp_TxnRef) {
                return ApiResponse(false, [], Response::HTTP_BAD_REQUEST, 'Vui lòng kiểm tra lại');
            }
            if ($request->vnp_TransactionStatus == 00) {
                $booking->status = 'Payment successful';
                $booking->save();
                $seats = Ticket::where('booking_id', $request->vnp_TxnRef)->pluck('seat_id')->toArray();
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
                $transaction->booking_id = $request->vnp_TxnRef;
                $transaction->subtotal = $booking->subtotal;
                $transaction->payment_method = 'Vnpay';
                $transaction->status = 'Đã thanh toán';
                $transaction->save();
                $seatIds = Ticket::where('booking_id', $request->vnp_TxnRef)->pluck('seat_id')->toArray();
                $seats = Seat::whereIn('id', $seatIds)->get();
                $seatDetails = $seats->mapWithKeys(function ($seat) {
                    return [
                        $seat->seat_number => [
                            'seat_price' => $seat->seatType->price ?? 0,
                            'seat_type_name' => $seat->seatType->name ?? ''
                        ]
                    ];
                });
                $seatTypeName = $seats->first()->seatType->name ?? '';
                $serviceIds = BookingService::where('booking_id', $request->vnp_TxnRef)->pluck('service_id');
                $services = Service::whereIn('id', $serviceIds)->get();
                $serviceDetails = [];
                $bookingServices = BookingService::where('booking_id', $request->vnp_TxnRef)->get();
                foreach ($bookingServices as $bookingService) {
                    $service = $services->firstWhere('id', $bookingService->service_id);
                    if ($service) {
                        $serviceDetails[$service->name] = [
                            'quantity' => $bookingService->quantity,
                            'price' => $bookingService->subtotal / $bookingService->quantity,
                            'total' => $bookingService->subtotal,
                        ];
                    }
                }
                $totalServicePrice = $bookingServices->sum('subtotal');
                $seats = Ticket::where('booking_id', $request->vnp_TxnRef)->pluck('seat_id')->toArray();
                $seatShowtimes = SeatShowtime::with('seat.seatType')
                    ->whereIn('seat_id', $seats)
                    ->get();
                $showtime = $booking->showtime;
                $showDate = Carbon::parse($showtime->show_date)->dayOfWeek;
                // Lấy giá ghế với điều kiện ngày cuối tuần
                $seatPrice = $seatShowtimes->first()->seat->seatType->price ?? 0;
                $seatPrice = ($showDate === Carbon::SATURDAY || $showDate === Carbon::SUNDAY)
                    ? ($seatShowtimes->first()->seat->seatType->promotion_price ?? $seatPrice)
                    : $seatPrice;

                $seatPrice = $seatShowtimes->isEmpty() ? 0 : $seatPrice;
                $numberOfSeats = count($seats);
                $seatPrice = floatval($seatPrice);
                $seatDetails = [
                    'seat_numbers' => $seatDetails->keys(),
                    'seat_ids' => $seats,
                    'seat_price' => $seatPrice,
                    'price' => $numberOfSeats * $seatPrice,
                    'services' => $serviceDetails,
                    'total_service_price' => $totalServicePrice,
                    'seat_types' => $seatTypeName
                ];
                // Gửi email xác nhận
                $barcodeUrl = $booking->code;
                $cinema = $booking->showtime->cinemaScreen->cinema;
                $showDate = $booking->showtime->show_date;
                $showTime = $booking->showtime->show_time;
                $totalAmount = $booking->subtotal;
                Mail::to($booking->user->email)->send(new BookingConfirmationMail(
                    $booking,
                    $barcodeUrl,
                    $cinema,
                    $showDate,
                    $showTime,
                    $seatDetails,
                    $totalAmount
                ));
                DB::commit();
                return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
            } elseif ($request->vnp_TransactionStatus == 02) {
                try {
                    $seats = Ticket::where('booking_id', $request->vnp_TxnRef)->pluck('seat_id')->toArray();
                    foreach ($seats as $seatId) {
                        $seatShowtime = SeatShowtime::where('seat_id', $seatId)
                            ->where('showtime_id', $booking->showtime_id)
                            ->first();
                        if ($seatShowtime) {
                            $seatShowtime->user_id = null;
                            $seatShowtime->save();
                        }
                    }
                    Ticket::where('booking_id', $request->vnp_TxnRef)->delete();
                    $bookingServices = BookingService::where('booking_id', $request->vnp_TxnRef)->get();
                    foreach ($bookingServices as $bookingService) {
                        $bookingService->delete();
                    }
                    Booking::where('id', $request->vnp_TxnRef)->delete();
                    DB::commit();
                    return ApiResponse(false, null, Response::HTTP_OK, messageResponseActionFailed());
                } catch (\Exception $e) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
