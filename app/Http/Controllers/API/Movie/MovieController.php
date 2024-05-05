<?php

namespace App\Http\Controllers\API\Movie;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Http\Response;
use App\Http\Requests\API\Movie\MovieRequest;
use App\Http\Resources\API\Movie\MovieResource;
use Illuminate\Support\Facades\Config;

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
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Movie::where('deleted',0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'data' => MovieResource::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ],
            ];
            return ApiResponse(true,$result, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //POST api/dashboard/movie/create
    public function store(MovieRequest $request)
    {
        try {
            $movie = Movie::create($request->all());
            if(!$movie){
               return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,messageResponseActionFailed() );
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    
    //UPDATE api/dashboard/movie/update/{id}
    public function update(MovieRequest $request, string $id){
        try {
            $movie = Movie::find($id);
            empty($movie) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $movieUpdated = Movie::where('id', $id)->update($request->all());
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }       
    }
    
    //DELETE api/dashboard/role/delete/{id}
    public function destroy(string $id){
        try {
            $movie = Movie::find($id);
            empty($movie) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $movie->deleted = 1;
            $movie->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }    
    }
}
