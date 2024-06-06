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
    // filter show times
    public function filter(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $query = Showtime::query();
            $query->where('deleted', 0);
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
            if (!$request->has('date') && !$request->has('city') && !$request->has('experiences')) {
                $query->orderBy($this->sort, $this->order);
                $data = $query->paginate($this->limit);
                $result = [
                    'showtimes' => ShowtimeResource::collection($data),
                    'meta' => [
                        'total' => $data->total(),
                        'perPage' => $data->perPage(),
                        'currentPage' => $data->currentPage(),
                        'lastPage' => $data->lastPage(),
                    ],
                ];
                return ApiResponse(true, $result, Response::HTTP_OK, messageResponseActionSuccess());
            }
            $result = $query->with('cinemaScreen.cinema', 'movie', 'cinemaScreen.screen')->get();
            if ($result->isEmpty()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có xuất chiếu nào');
            }
            $list = $result->map(function ($showtime) {
                return [
                    'cinema_city' => $showtime->cinemaScreen->cinema->city,
                    'cinema_name' => $showtime->cinemaScreen->cinema->name,
                    'screen_name' => $showtime->cinemaScreen->screen->name,
                    'subtitle' => $showtime->subtitle,
                    'movie_name' => $showtime->movie->title,
                    'show_date' => $showtime->show_date,
                    'show_time' => $showtime->show_time,
                    'status' => $showtime->status,
                ];
            });
            return ApiResponse(true, $list, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //filterMovie
    public function filterMovie(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $query = Showtime::query();
            $query->where('deleted', 0);
            if ($request->has('date')) {
                $query->where('show_date', $request->date);
            }
            if ($request->has('city')) {
                $query->whereHas('cinemaScreen.cinema', function ($q) use ($request) {
                    $q->where('city', $request->city);
                });
            }
            if ($request->has('genre')) {
                $query->whereHas('movie', function ($q) use ($request) {
                    $q->where('genre', $request->genre);
                });
            }
            if ($request->has('name')) {
                $query->whereHas('cinemaScreen.cinema', function ($q) use ($request) {
                    $q->where('name', $request->name);
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
            if (!$request->has('date') && !$request->has('city') && !$request->has('experiences') && !$request->has('name')) {
                $query->orderBy($this->sort, $this->order);
                $data = $query->paginate($this->limit);
                $result = [
                    'showtimes' => ShowtimeResource::collection($data),
                    'meta' => [
                        'total' => $data->total(),
                        'perPage' => $data->perPage(),
                        'currentPage' => $data->currentPage(),
                        'lastPage' => $data->lastPage(),
                    ],
                ];
                return ApiResponse(true, $result, Response::HTTP_OK, messageResponseActionSuccess());
            }
            $result = $query->with('cinemaScreen.cinema', 'movie', 'cinemaScreen.screen')->get();
            if ($result->isEmpty()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có xuất chiếu nào');
            }
            $list = $result->map(function ($movie) {
                return [
                    'cinema_city' => $movie->cinemaScreen->cinema->city,
                    'cinema_name' => $movie->cinemaScreen->cinema->name,
                    'screen_name' => $movie->cinemaScreen->screen->name,
                    'subtitle' => $movie->subtitle,
                    'movie_name' => $movie->movie->title,
                    'movie_genre' => $movie->movie->genre,
                    'movie_duration' => $movie->movie->duration,
                    'show_date' => $movie->show_date,
                    'show_time' => $movie->show_time,
                ];
            });
            return ApiResponse(true, $list, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
