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
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Hiển thị danh sách ghế theo seatmap dựa vào showtime_id
     *
     * @param Request $request
     * @return mixed
     * @throws BindingResolutionException
     *
     * GET api/show-seat-map/{showtime_id}
     */
    public function showSeatMap($showtime_id)
    {
        $data = ['showtime_id' => $showtime_id];
        $validator = Validator::make($data, [
            'showtime_id' => 'required|exists:showtimes,id',
        ], [
            'showtime_id.required' => 'Vui lòng cung cấp ID của showtime.',
            'showtime_id.exists' => 'ID của showtime không hợp lệ.',
        ]);
        if ($validator->fails()) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $validator->errors());
        }
        try {
            $user_id = auth('sanctum')->user()->id;
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
            $seatAll = SeatShowtime::with('seat.seatType', 'seat.seatShowtime')
            ->where('showtime_id', $showtime_id)
            ->get()
            ->pluck('seat')
            ->filter(function ($seat) {
                return $seat->deleted == 0;
            })
            ->sort(function ($a, $b) {
                return $this->sortSeatNumbers($a->seat_number, $b->seat_number);
            });
            $detail = [];
            $characterArr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'N'];
            foreach ($seatAll as $item) {
                $rowIndex = array_search($item['seat_number'][0], $characterArr);
                if ($rowIndex !== false) {
                    if ($item->status != Seat::STATUS_OCCUPIED) {
                        $detail[$rowIndex][] = [
                            'id' => $item['id'],
                            'seat_number' => $item['seat_number'],
                            'type' => $item->seatType->name,
                            'price' => $item->seatType->price,
                            'status' => Seat::STATUS_UNOCCUPIED,
                        ];
                    } else {
                        $status = $item->seatShowtime ? $this->getSeatShowtimeStatus($item->seatShowtime, $user_id) : SeatShowtime::STATUS_AVAILABLE;
                        $detail[$rowIndex][] = [
                            'id' => $item['id'],
                            'seat_number' => $item['seat_number'],
                            'type' => $item->seatType->name,
                            'price' => $item->seatType->price,
                            'status' => $status,
                        ];
                    }
                }
            }
            for ($i = 0; $i < count($layoutArr); $i++) {
                $count = countUniqueCharacters($layoutArr[$i]);
                if ($count == 0) {
                    $noSeat = [];
                    array_splice($detail, $i, 0, [$noSeat]);
                } else {
                    if ($i >= 1) {
                        $seatChecks = Seat::where('seat_number', 'LIKE',  $characterArr[$i - 1] . '%')
                            ->where('cinema_screen_id', $seatMap->cinema_screen_id)
                            ->first();
                        if (!$seatChecks) {
                            $layoutRow = str_replace('X', '', $layoutArr[$i]);
                            for ($z = 0; $z < Str::length($layoutRow); $z++) {
                                $detail[$i][] = [
                                    'id' => '-',
                                    'seat_number' => '-',
                                    'type' => '',
                                    'price' => '-',
                                    'status' => '',
                                ];
                            }
                        }
                    }
                    for ($j = 0; $j < Str::length($layoutArr[$i]); $j++) {
                        if ($layoutArr[$i][$j] == 'X') {
                            $noSeatNumber = [
                                [
                                    'type' => 'X',
                                ]
                            ];
                            array_splice($detail[$i], $j, 0, $noSeatNumber);
                        }
                    }
                }
            }
            ksort($detail);
            $data = [
                'movie_title' => $showtime->movie->title,
                'cinema_name' => $showtime->cinemaScreen->cinema->name,
                'city' => $showtime->cinemaScreen->cinema->city,
                'showtime' => $showtime->show_time,
                'show_date' => $showtime->show_date,
                'screen' => $showtime->cinemaScreen->screen->name,
                'seats' => $detail,
            ];

            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Sắp xếp dữ liệu seatAll
     *
     * @param mixed $seatNumberA
     * @param mixed $seatNumberB
     * @return int
     */
    private function sortSeatNumbers($seatNumberA, $seatNumberB)
    {
        preg_match('/([A-Za-z]+)(\d+)/', $seatNumberA, $matchesA);
        preg_match('/([A-Za-z]+)(\d+)/', $seatNumberB, $matchesB);
        $letterComparison = strcmp($matchesA[1], $matchesB[1]);
        if ($letterComparison !== 0) {
            return $letterComparison;
        }
        return (int)$matchesA[2] - (int)$matchesB[2];
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
