<?php

namespace App\Http\Controllers\API\Client\Movie;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Movie\MovieResource;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelIgnition\Recorders\DumpRecorder\Dump;

class MovieController extends Controller
{
    // Get api/client/movie (truyền status: không truyền-tất cả, 1-đang chiếu, 2-sắp chiếu)
    public function index(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            if ($request->status == 1) {
                $data = Movie::where('deleted', 0)->where('status', Movie::STATUS_CURRENTLY)->orderBy($this->sort, $this->order)->paginate($this->limit);
            } elseif ($request->status == 2) {
                $data = Movie::where('deleted', 0)->where('status', Movie::STATUS_COMING)->orderBy($this->sort, $this->order)->paginate($this->limit);
            } else {
                $data = Movie::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            }
            $result = [
                'movie' => MovieResource::collection($data),
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
    // Get api/client/movie/{id} 
    public function show($id)
    {
        try {
            $movie = Movie::where('id', $id)->where('deleted', 0)->first();
            empty($movie) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'movie' => new MovieResource($movie),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
