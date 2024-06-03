<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Client\ClientRequest;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Seat;
use App\Models\SeatType;
use App\Models\Service;
use App\Models\Ticket;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use DivisionByZeroError;
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
    public function updateTickets(ClientRequest $request)
    {
        $subtotal = 0;

        DB::beginTransaction();

        try {
            $user = auth('sanctum')->user();
            $booking = Booking::create([
                'user_id' => $user->id,
                'quantity' => count($request->seats),
                'subtotal' => $subtotal,
                'status' => 'Pending',
            ]);

            $barcode = new DNS1D();
            $barcodeString = $barcode->getBarcodePNG(uniqid(), 'C128', 3, 33);

            $tempBarcodePath = tempnam(sys_get_temp_dir(), 'barcode') . '.png';
            file_put_contents($tempBarcodePath, base64_decode($barcodeString));

            $uploadedFileUrl = Cloudinary::uploadFile($tempBarcodePath, [
                'folder' => 'ticket'
            ])->getSecurePath();

            unlink($tempBarcodePath);

            foreach ($request->seats as $seatId) {
                $ticket = Ticket::where('showtime_id', $request->showtime_id)
                    ->where('seat_id', $seatId)
                    ->first();

                if (!$ticket) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không tìm thấy vé cho chỗ đã đặt');
                }

                $seat = Seat::findOrFail($seatId);
                if ($seat->status !== Seat::STATUS_UNOCCUPIED) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Ghế đã được đặt');
                }

                $seatType = SeatType::findOrFail($seat->seat_type_id);
                $seatPrice = $seatType->price;
                $subtotal += $seatPrice;

                $seat->update(['status' => Seat::STATUS_OCCUPIED]);

                $ticket->update([
                    'booking_id' => $booking->id,
                    'code' => $uploadedFileUrl,
                    'status' => Ticket::STATUS_RESERVED,
                ]);
            }

            if ($request->has('services')) {
                $serviceSummary = [];
                $totalServicesForBooking = 0;

                foreach ($request->services as $service) {
                    $serviceModel = Service::findOrFail($service['service_id']);

                    if ($serviceModel->quantity < $service['quantity']) {
                        DB::rollBack();
                        return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Dịch vụ ' . $serviceModel->name . ' đã hết');
                    }

                    $serviceModel->decrement('quantity', $service['quantity']);

                    $totalServicesForBooking += $service['quantity'];

                    if ($totalServicesForBooking > 3 * count($request->seats)) {
                        DB::rollBack();
                        return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Mỗi vé chỉ được tối đa 3 dịch vụ');
                    }

                    if (isset($serviceSummary[$service['service_id']])) {
                        $serviceSummary[$service['service_id']]['quantity'] += $service['quantity'];
                        $serviceSummary[$service['service_id']]['subtotal'] += $serviceModel->price * $service['quantity'];
                    } else {
                        $serviceSummary[$service['service_id']] = [
                            'service_id' => $service['service_id'],
                            'quantity' => $service['quantity'],
                            'subtotal' => $serviceModel->price * $service['quantity'],
                        ];
                    }

                    $subtotal += $serviceModel->price * $service['quantity'];
                }

                foreach ($serviceSummary as $summary) {
                    BookingService::create([
                        'booking_id' => $booking->id,
                        'service_id' => $summary['service_id'],
                        'quantity' => $summary['quantity'],
                        'subtotal' => $summary['subtotal'],
                    ]);
                }
            }

            $booking->update(['subtotal' => $subtotal]);

            DB::commit();

            return ApiResponse(true, $booking, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
