<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Showtime\ShowtimeResource;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class FilterController extends Controller
{
    // lấy tất cả thông tin xuất chiếu kèm rạp
    public function index(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Showtime::where('deleted', 0)
                ->with('cinemaScreen.cinema')
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);
            $result = [
                'showtimes' => ShowtimeResource::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
     // tìm kiếm theo điều kiện
    public function filter(Request $request)
    {
        $query = Showtime::query();
        if ($request->has('date')) {
            $query->where('show_date', $request->date);
        }
        if ($request->has('city')) {
            $query->whereHas('cinemaScreen.cinema', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }
        if ($request->has('experiences')) {
            $experiences = explode(':', $request->experiences);
            if (count($experiences) === 2) {
                $name = $experiences[0];
                $subtitle = $experiences[1];
                $query->whereHas('cinemaScreen.screen', function ($q) use ($name) {
                    $q->where('name', $name);
                });
                $query->where('subtitle', $subtitle);
            }
        }
        $result = $query->with('cinemaScreen.cinema','movie')->get();
        if ($result->isEmpty()) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có xuất chiếu nào');
        }
        $list = $result->map(function ($showtime) {
            return [
                'cinema_name' => $showtime->cinemaScreen->cinema->name,
                'movie_name' => $showtime->movie->title,
                'subtitle' => $showtime->subtitle,
                'show_date' => $showtime->show_date,
                'show_time' => $showtime->show_time,
                'status' => $showtime->status,
            ];
        });
        return ApiResponse(true, $list, Response::HTTP_OK, messageResponseActionSuccess());
    }
}
