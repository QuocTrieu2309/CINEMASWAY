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
            $data = $request->except('image');
            if ($request->hasFile('image')) {
                $uploadedImage = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'CINEMASWAY/MOVIE',
                    'overwrite' => true,
                    'resource_type' => 'image'
                ]);
                $imageUrl = $uploadedImage->getSecurePath();
                $data['image'] = $imageUrl;
            }
            $movie = Movie::create($data);
            if (!$movie && isset($imageUrl)) {
                $publicId = getImagePublicId($imageUrl);
                Cloudinary::destroy($publicId);
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
            $data = $request->except('image');
            $imageOld = $movie->image;
            if ($request->hasFile('image')) {
                $uploadedImage = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'CINEMASWAY/MOVIE',
                    'overwrite' => true,
                    'resource_type' => 'image'
                ]);
                $imageUrl = $uploadedImage->getSecurePath();
                $data['image'] = $imageUrl;
            } else {
                $data['image'] = $imageOld;
            }
            $movieUpdated = $movie->update($data);
            if (!$movieUpdated && isset($imageUrl)) {
                $publicId = getImagePublicId($imageUrl);
                Cloudinary::destroy($publicId);
                throw new \ErrorException('Cập nhật không thành công', Response::HTTP_BAD_REQUEST);
            }
            if ($movieUpdated && isset($imageUrl) && $imageOld) {
                $publicId = getImagePublicId($imageOld);
                Cloudinary::destroy($publicId);
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
            $movie = Movie::where('id', $id)->where('deleted', 0)->first();
            empty($movie) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $movie->deleted = 1;
            $movie->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
