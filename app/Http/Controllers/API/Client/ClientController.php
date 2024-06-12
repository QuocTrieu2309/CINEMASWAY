<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Client\ClientRequest;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Service;
use App\Models\Ticket;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use DivisionByZeroError;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;
use Illuminate\Http\Response;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * POST api/booking
     *
     * @param ClientRequest $request
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws DivisionByZeroError
     */
    public function createBooking(ClientRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = auth('sanctum')->user();
            $barcode = new DNS1D();
            $barcodeString = $barcode->getBarcodePNG(uniqid(), 'C128', 3, 33);

            $tempBarcodePath = tempnam(sys_get_temp_dir(), 'barcode') . '.png';
            file_put_contents($tempBarcodePath, base64_decode($barcodeString));

            $uploadedFileUrl = Cloudinary::uploadFile($tempBarcodePath, [
                'folder' => 'Booking'
            ])->getSecurePath();

            unlink($tempBarcodePath);
            $booking = Booking::create([
                'user_id' => $user->id,
                'showtime_id'=>$request->showtime_id,
                'code' => $uploadedFileUrl,
                'quantity' => count($request->seats),
                'subtotal' => $request->subtotal,
                'status' => Booking::STATUS_UNPAID,
            ]);
            if (!$booking) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            foreach ($request->seats as $seatId) {
                $cridential = Ticket::query()->create([
                    'booking_id' => $booking->id,
                    'seat_id' => $seatId
                ]);
                if (!$cridential) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
                }
            }
            DB::commit();
            return ApiResponse(true, $booking, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    public function createBookingService(ClientRequest $request)
{
    DB::beginTransaction();

    try {
        $booking = Booking::find($request->booking_id);

        if (!$booking) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseNotFound());
        }

        $totalSubtotal = $booking->subtotal;

        foreach ($request->services as $service) {
            $serviceModel = Service::findOrFail($service['service_id']);

            if ($serviceModel->quantity < $service['quantity']) {
                DB::rollBack();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Dịch vụ ' . $serviceModel->name . ' không đủ số lượng để đáp ứng. Vui lòng giảm số lượng dịch vụ!');
            }

            $totalSubtotal += $service['subtotal'];

            $credential = BookingService::query()->create([
                'booking_id' => $booking->id,
                'service_id' => $service['service_id'],
                'quantity' => $service['quantity'],
                'subtotal' => $service['subtotal']
            ]);

            if (!$credential) {
                DB::rollBack();
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            $serviceModel->decrement('quantity', $service['quantity']);
        }
        $booking->subtotal = $totalSubtotal;
        $booking->save();
        DB::commit();
        return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
    }
}

}
