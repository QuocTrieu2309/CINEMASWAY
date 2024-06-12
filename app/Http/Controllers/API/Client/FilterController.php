<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
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
            $defaultDate = '2024-06-26';
            $defaultCity = 'ha noi';
            $defaultExperiences = '3d:VN';

            if ($request->has('date')) {
                $defaultDate = $request->date;
            }
            if ($request->has('city')) {
                $defaultCity = $request->city;
            }
            if ($request->has('experiences')) {
                $defaultExperiences = $request->experiences;
            }
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $query = Showtime::query();
            $query->where('deleted', 0);
            // xét dèault
            $query->where('show_date', $defaultDate);
            $query->whereHas('cinemaScreen.cinema', function ($q) use ($defaultCity) {
                $q->where('city', $defaultCity);
            });
            $experiences = explode(':', $defaultExperiences);
            if (count($experiences) === 2) {
                $name = $experiences[0];
                $subtitle = $experiences[1];
                $query->whereHas('cinemaScreen.screen', function ($q) use ($name) {
                    $q->where('name', $name);
                });
                $query->where('subtitle', $subtitle);
            }
            $data = $query->with('cinemaScreen.cinema', 'movie', 'cinemaScreen.screen')
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);
            if ($data->isEmpty()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có xuất chiếu nào');
            }
            $list = $data->map(function ($showtime) {
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
            $result = [
                'movies' => $list,
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //filter Movie city , date , cinemaName
    public function filterMovie(Request $request)
    {
        try {
            $defaultDate = '2024-06-12';
            $defaultCity = 'hai duong';
            $defaultName = 'rap2';
            if ($request->has('date')) {
                $defaultDate = $request->date;
            }
            if ($request->has('city')) {
                $defaultCity = $request->city;
            }
            if ($request->has('name')) {
                $defaultName = $request->name;
            }
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $query = Showtime::query();
            $query->where('deleted', 0);
            $query->where('show_date', $defaultDate);
            $query->whereHas('cinemaScreen.cinema', function ($q) use ($defaultCity, $defaultName) {
                $q->where('city', $defaultCity)
                    ->where('name', $defaultName);
            });
            $data = $query->with('cinemaScreen.cinema', 'movie', 'cinemaScreen.screen')
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);
            if ($data->isEmpty()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có xuất chiếu nào');
            }
            $list = $data->map(function ($movie) {
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
            $result = [
                'movies' => $list,
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //filter Movie màn hình screen
    public function filterScreenMovie(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $query = Showtime::query();
            $query->where('deleted', 0);
            if ($request->has('name')) {
                $query->whereHas('cinemaScreen.screen', function ($q) use ($request) {
                    $q->where('name', $request->name);
                });
            }
            $data = $query->with('cinemaScreen.cinema', 'movie', 'cinemaScreen.screen')
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);

            if ($data->isEmpty()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có xuất chiếu nào');
            }

            $list = $data->map(function ($movie) {
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
            $result = [
                'movies' => $list,
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //filter Movie theo thể loại
    public function filterGenreMovie(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $query = Showtime::query();
            $query->where('deleted', 0);
            if ($request->has('genre')) {
                $query->whereHas('movie', function ($q) use ($request) {
                    $q->where('genre', $request->genre);
                });
            }
            $data = $query->with('cinemaScreen.cinema', 'movie', 'cinemaScreen.screen')
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);
            if ($data->isEmpty()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có xuất chiếu nào');
            }
            $list = $data->map(function ($movie) {
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
            $result = [
                'movies' => $list,
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }


    //filter Movie theo phụ đề
    public function filterSubtitleMovie(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $query = Showtime::query();
            $query->where('deleted', 0);

            if ($request->has('subtitle')) {
                $query->where('subtitle', $request->subtitle);
            }
            $data = $query->with('cinemaScreen.cinema', 'movie', 'cinemaScreen.screen')
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);
            if ($data->isEmpty()) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có xuất chiếu nào');
            }
            $list = $data->map(function ($movie) {
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
            $result = [
                'movies' => $list,
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
