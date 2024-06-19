<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use App\Models\Cinema;

class FilterController extends Controller
{
    public function getCity(Request $request, $id)
    {
        try {
            $cridential = Cinema::find($id);
            if (!$cridential) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseNotFound());
            }
            $cinemas = Cinema::whereHas('cinemaScreens.showtimes', function ($query) use ($id) {
                $query->where('movie_id', $id);
            })->get();

            $cities = $cinemas->unique('city')->map(function ($cinema) {
                return [
                    'id' => $cinema->id,
                    'city' => $cinema->city
                ];
            });
            $cinemaNames = [];
            $count = 0;
            foreach ($cities as $city) {
                // Lấy danh sách các rạp chiếu phim ở thành phố $city có suất chiếu của bộ phim có id là $id
                $cinemas = Cinema::where('city', $city['city'])
                    ->whereHas('cinemaScreens.showtimes', function ($query) use ($id) {
                        $query->where('movie_id', $id);
                    })
                    ->with(['cinemaScreens.showtimes' => function ($query) use ($id) {
                        $query->where('movie_id', $id)
                              ->select('cinema_screen_id', 'show_time'); // Chỉ lấy các trường cần thiết
                    }])
                    ->get();

                // Lưu thông tin vào mảng kết quả
                $cinemaNames[] = [
                    'city'=> $city['city'],
                    'detail'=>$cinemas->map(function ($cinema) {
                        return [
                            'cinema' => $cinema->name,
                            'showtimes' => $cinema->cinemaScreens->flatMap(function ($screen) {
                                return $screen->showtimes->map(function ($showtime) {
                                    return $showtime->show_time;
                                });
                            }),
                        ];
                    })
                ];
            }

            return ApiResponse(true, $cinemaNames, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    // filter show times
    public function filter(Request $request, $id)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $query = Showtime::query();
            $query->where('deleted', 0);
            // xét dèault
            if ($request->has('date')) {
                $query->where('show_date', $request->date);
            }
            if ($request->has('city')) {
                $city = $request->city;
                $query->whereHas('cinemaScreen.cinema', function ($q) use ($city) {
                    $q->where('city', $city);
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
                    'movie_duration' => $showtime->movie->duration,
                    'show_date' => $showtime->show_date,
                    'show_time' => $showtime->show_time,
                    'id_showtime' => $showtime->id,
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
                    'id_showtime' => $movie->id,

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
                    'id_showtime' => $movie->id,

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
                    'id_showtime' => $movie->id,
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
                    'id_showtime' => $movie->id,

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
