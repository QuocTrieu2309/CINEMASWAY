<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Service\ServiceResource;
use App\Models\Seat;
use App\Models\SeatMap;
use App\Models\SeatShowtime;
use App\Models\Service;
use App\Models\Showtime;
use Carbon\Carbon;
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
            $currentDateTime = Carbon::now('Asia/Ho_Chi_Minh');
            $numberCurrentDate = $currentDateTime->dayOfWeek;
            $showtime = Showtime::with('cinemaScreen.seatMaps', 'cinemaScreen.seats.seatType', 'cinemaScreen.seats.seatShowtime')
                ->findOrFail($showtime_id);
            $cinemaScreen = $showtime->cinemaScreen;
            $showDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $showtime->show_date . ' ' . $showtime->show_time,'Asia/Ho_Chi_Minh');
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
            $seats = Seat::with('seatType')
                ->where('cinema_screen_id', $seatMap->cinema_screen_id)
                ->where('deleted', 0)
                ->get()
                ->sortBy(function ($seat) {
                    return $this->sortSeatNumbers($seat->seat_number, $seat->seat_number);
                });

            $seatShowtimes = SeatShowtime::where('showtime_id', $showtime_id)->get()->keyBy('seat_id');

            $detail = [];
            $characterArr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'N'];

            foreach ($seats as $seat) {
                $rowIndex = array_search($seat->seat_number[0], $characterArr);
                if ($rowIndex !== false) {
                    $seatShowtime = $seatShowtimes->get($seat->id);
                    if ($seatShowtime) {
                        $status = $this->getSeatShowtimeStatus($seatShowtime, $user_id);
                    } else {
                        $status = Seat::STATUS_UNOCCUPIED;
                    }
                    if ($status == Seat::STATUS_UNOCCUPIED) {
                        $detail[$rowIndex][] = [
                            'id' => $seat->id,
                            'seat_number' => $seat->seat_number,
                            'status' => $status,
                        ];
                    } else {
                        $detail[$rowIndex][] = [
                            'id' => $seat->id,
                            'seat_number' => $seat->seat_number,
                            'type' => $seat->seatType->name,
                            'price' => ($numberCurrentDate === Carbon::SATURDAY || $numberCurrentDate === Carbon::SUNDAY)
                            ? $seat->seatType->promotion_price
                            : $seat->seatType->price,
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
            if($currentDateTime->lessThan($showDateTime)) {
                return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
            } else {
                return ApiResponse(false, null, Response::HTTP_FORBIDDEN, 'Suất chiếu đã hết hạn.');
            }
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
        if ($seatShowtime->status === SeatShowtime::STATUS_RESERVED) {
            return SeatShowtime::STATUS_RESERVED;
        } elseif ($seatShowtime->user_id !== null && $seatShowtime->user_id !== $user_id) {
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
     * truyền vào params id : seat_id, showtime_id
     */
    public function updateStatusSeat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:seats,id',
            'showtime_id' => 'required|exists:seat_showtimes,id',
        ], [
            'id.required' => 'Vui lòng cung cấp ID của ghế.',
            'id.exists' => 'ID của ghế không hợp lệ.',
            'showtime_id.required' => 'Vui lòng cung cấp ID của suất chiếu.',
            'showtime_id.exists' => 'ID của suất chiếu không hợp lệ.',
        ]);
        if ($validator->fails()) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $validator->errors());
        }
        DB::beginTransaction();
        try {
            $user_id = auth('sanctum')->user()->id;
            $seatShowtime = SeatShowtime::where('seat_id', $request->id)
                ->where('showtime_id', $request->showtime_id)
                ->first();
            if (!$seatShowtime) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Seat not found');
            }
            if (isset($seatShowtime->user_id)) {
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'Seat is already reserved by another user.');
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
     * truyền vào params seat_ids (mảng seat_id), showtime_id
     */
    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seat_ids' => 'required',
            'seat_ids.*' => 'required|exists:seats,id',
            'showtime_id' => 'required|exists:showtimes,id',
        ], [
            'seat_ids.required' => 'Vui lòng cung cấp ID của các ghế.',
            'seat_ids.array' => 'Các ID của ghế phải là một mảng.',
            'seat_ids.*.required' => 'Vui lòng cung cấp ID của ghế.',
            'seat_ids.*.exists' => 'ID của ghế không hợp lệ.',
            'showtime_id.required' => 'Vui lòng cung cấp ID của suất chiếu.',
            'showtime_id.exists' => 'ID của suất chiếu không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $validator->errors());
        }

        DB::beginTransaction();
        try {
            $user_id = auth('sanctum')->user()->id;
            $seat_ids = $request->seat_ids;
            $seatShowtimes = SeatShowtime::whereIn('seat_id', $seat_ids)
                ->where('showtime_id', $request->showtime_id)
                ->get();

            if ($seatShowtimes->isEmpty() || $seatShowtimes->count() != count($seat_ids)) {
                DB::rollBack();
                return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, 'One or more seats not found');
            }

            foreach ($seatShowtimes as $seatShowtime) {
                if ($seatShowtime->user_id !== $user_id) {
                    DB::rollBack();
                    return ApiResponse(false, null, Response::HTTP_UNAUTHORIZED, 'Unauthorized action.');
                }
            }
            SeatShowtime::whereIn('id', $seatShowtimes->pluck('id'))
                ->update(['user_id' => null]);

            DB::commit();

            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //get All service
    public function getService()
    {
        try {
            $services = Service::where('deleted', 0)->get();
            $data = ServiceResource::collection($services);
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
