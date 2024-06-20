<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use App\Models\SeatMap;
use App\Models\SeatShowtime;
use App\Models\Showtime;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ChooseSeatController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:sanctum');
    // }

    /**
     * Hiển thị danh sách ghế theo seatmap dựa vào showtime_id
     *
     * @param Request $request
     * @return mixed
     * @throws BindingResolutionException
     *
     * POST api/show-seat-map
     */
    public function showSeatMap(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'showtime_id' => 'required|exists:showtimes,id',
        ], [
            'showtime_id.required' => 'Vui lòng cung cấp ID của showtime.',
            'showtime_id.exists' => 'ID của showtime không hợp lệ.',
        ]);
        if ($validator->fails()) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $validator->errors());
        }
        try {
            // $user_id = auth('sanctum')->user()->id;
            $user_id = 1;
            $showtime_id = $request->showtime_id;
            $showtime = Showtime::with('cinemaScreen.seatMaps', 'cinemaScreen.seats.seatType', 'cinemaScreen.seats.seatShowtime')
                ->findOrFail($showtime_id);
            $cinemaScreen = $showtime->cinemaScreen;
            if (!$cinemaScreen) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Không tìm thấy Cinema Screen');
            }
            $seatMap = SeatMap::where('cinema_screen_id', $cinemaScreen->id)
                ->where('deleted', 0)
                ->first();
            if (!$seatMap) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Không tìm thấy Seat Map');
            }
            $layoutArr = explode('|', $seatMap->layout);
            $seatAll = Seat::with('seatType', 'seatShowtime')
                ->where('cinema_screen_id', $seatMap->cinema_screen_id)
                ->where('status', Seat::STATUS_OCCUPIED)
                ->where('deleted', 0)
                ->get();
            $detail = [];
            $characterArr = ['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'K', 'L', 'M', 'N'];
            foreach ($seatAll as $item) {
                $count = 0;
                foreach ($characterArr as $character) {
                    if ($item['seat_number'][0] == $character) {
                        $status = $item->seatShowtime ? $this->getSeatShowtimeStatus($item->seatShowtime, $user_id) : SeatShowtime::STATUS_AVAILABLE;
                        $detail[$count][] = [
                            'id' => $item['id'],
                            'seat_number' => $item['seat_number'],
                            'type' => $item->seatType->name,
                            'price' => $item->seatType->price,
                            'status' => $status,
                        ];
                    }
                    $count++;
                }
            }
            for ($i = 0; $i < count($layoutArr); $i++) {
                $count = $this->countUniqueCharacters($layoutArr[$i]);
                if ($count == 0) {
                    $noSeat[] = [];
                    array_splice($detail, $i, 0, $noSeat);
                } else {
                    for ($j = 0; $j < Str::length($layoutArr[$i]); $j++) {
                        if ($layoutArr[$i][$j] == 'X') {
                            $noSeatNumber = [
                                0 => [
                                    'id' => null,
                                    'seat_number' => 0,
                                    'type' => null,
                                    'price' => 0,
                                    'status' => null,
                                ]
                            ];
                            array_splice($detail[$i], $j, 0, $noSeatNumber);
                        }
                    }
                }
            }

            $data = [
                'movie_title'   => $showtime->movie->title,
                'cinema_name'   => $showtime->cinemaScreen->cinema->name,
                'city'   => $showtime->cinemaScreen->cinema->city,
                'showtime'   => $showtime->show_time,
                'show_date'   => $showtime->show_date,
                'screen'   => $showtime->cinemaScreen->screen->name,
                'seats' => $detail,
            ];

            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    private function countUniqueCharacters($str)
    {
        return count(array_unique(str_split($str)));
    }

    /**
     * Kiểm tra status seatShowtime
     *
     * @param mixed $seatShowtime
     * @param mixed $user_id
     * @return string
     */
    private function getSeatShowtimeStatus($seatShowtime, $user_id)
    {
        if ($seatShowtime->user_id !== null && $seatShowtime->user_id !== $user_id) {
            return SeatShowtime::STATUS_HELD;
        } elseif ($seatShowtime->user_id === $user_id) {
            return SeatShowtime::STATUS_SELECTED;
        } else {
            return SeatShowtime::STATUS_AVAILABLE;
        }
    }

    /**
     * Cập nhật status seatShowtime khi chọn ghế
     *
     * @param Request $request
     * @return mixed
     * @throws BindingResolutionException
     *
     * POST api/status
     */
    public function updateStatusSeat(Request $request)
    {
        $user_id = auth('sanctum')->user()->id;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:seats,id',
        ], [
            'id.required' => 'Vui lòng cung cấp ID của ghế.',
            'id.exists' => 'ID của ghế không hợp lệ.',
        ]);
        if ($validator->fails()) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $validator->errors());
        }
        DB::beginTransaction();
        try {
            $seatShowtime = Seat::findOrFail($request->id)->seatShowtime;
            if (!$seatShowtime) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Seat not found');
            }
            if (isset($seatShowtime->user_id)) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Something Fails');
            }
            $seatShowtime->update([
                'user_id' => $user_id
            ]);
            DB::commit();

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Hủy chọn ghế
     *
     * @param Request $request
     * @return mixed
     * @throws BindingResolutionException
     *
     * POST api/cancel
     */
    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:seats,id',
        ], [
            'id.required' => 'Vui lòng cung cấp ID của ghế.',
            'id.exists' => 'ID của ghế không hợp lệ.',
        ]);
        if ($validator->fails()) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $validator->errors());
        }
        DB::beginTransaction();
        try {
            $seatShowtime = Seat::findOrFail($request->id)->seatShowtime;
            if (!$seatShowtime) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Seat not found');
            }
            $seatShowtime->update([
                'user_id' => null
            ]);
            DB::commit();

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
