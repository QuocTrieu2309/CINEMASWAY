<?php

namespace App\Http\Controllers\API\Movie;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Http\Response;
use App\Http\Requests\API\Movie\MovieRequest;
use App\Http\Resources\API\Movie\MovieResource;
use Illuminate\Support\Facades\Config;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/movie
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Movie::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Movie::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $current_date = now()->format('Y-m-d');
            foreach ($data as $movie) {
                if($current_date > $movie->end_date) {
                    $movie->status = Movie::STATUS_STOPPED;
                    $movie->is_early_showtime = 0;
                    $movie->save();
                } else if ($current_date >= $movie->release_date && $current_date <= $movie->end_date) {
                    $movie->status = Movie::STATUS_CURRENTLY;
                    $movie->is_early_showtime = 0;
                    $movie->save();
                }
            }
            $result = [
                'movies' => MovieResource::collection($data),
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

    // GET /api/dashboard/movie/{id}
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', Movie::class);
            $movie = Movie::where('id', $id)->where('deleted', 0)->first();
            empty($movie) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'movie' => new  MovieResource($movie),
            ];
            return ApiResponse(true,   $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //POST api/dashboard/movie/create
    public function store(MovieRequest $request)
    {
        try {
            $this->authorize('checkPermission', Movie::class);
            $data = $request->all();
            $movie = Movie::create($data);
            if (!$movie) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //UPDATE api/dashboard/movie/update/{id}
    public function update(MovieRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', Movie::class);
            $movie = Movie::where('id', $id)->where('deleted', 0)->first();
            if (!$movie) {
                return ApiResponse(false, null, Response::HTTP_NOT_FOUND, messageResponseNotFound());
            }
            $currentDate = now()->toDateString();
            $hasUpcomingShowtimes = $movie->showtimes()->where('show_date', '>=', $currentDate)
                ->where('deleted', 0)
                ->whereHas('bookings', function ($query) {
                    $query->where('status', 'Payment successful');
                })
                ->exists();
            if ($hasUpcomingShowtimes) {
                return ApiResponse(false, null, Response::HTTP_FORBIDDEN, "Không thể cập nhật phim khi phim vẫn còn xuất chiếu đang hoạt động và có vé đã được đặt.");
            }
            $data = $request->all();
            $cridential = $movie->update($data);
            if (!$cridential) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //DELETE api/dashboard/movie/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Movie::class);
            DB::beginTransaction();
            $movie = Movie::where('id', $id)->where('deleted', 0)->first();
            empty($movie) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $currentDate = now()->toDateString();
            $hasUpcomingShowtimes = $movie->showtimes()->where('show_date', '>=', $currentDate)
                ->where('deleted', 0)
                ->whereHas('bookings', function ($query) {
                    $query->where('status', 'Payment successful');
                })
                ->exists();
            if ($hasUpcomingShowtimes) {
                return ApiResponse(false, null, Response::HTTP_FORBIDDEN, "Không thể xóa phim khi phim vẫn còn xuất chiếu đang hoạt động và có vé đã được đặt.");
            }
            $hasRelatedRecords = $movie->showtimes()->exists();
            if ($hasRelatedRecords) {
                $movie->deleted = 1;
                $movie->save();
            } else {
                $movie->delete();
            }
            DB::commit();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
