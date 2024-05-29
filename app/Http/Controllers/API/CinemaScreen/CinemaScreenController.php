<?php

namespace App\Http\Controllers\API\CinemaScreen;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CinemaScreen\CinemaScreenRequest;
use App\Http\Resources\API\CinemaScreen\CinemaScreenResource;
use App\Models\CinemaScreen;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class CinemaScreenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/cinema-screen
    public function index(Request $request)
    {
        try {
            // $this->authorize('checkPermission',CinemaScreen::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = CinemaScreen::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'cinemaScreens' => CinemaScreenResource::collection($data),
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
    // GET /api/dashboard/cinema-screen/{id}
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', CinemaScreen::class);
            $cinemaScreen = CinemaScreen::where('id', $id)->where('deleted', 0)->first();
            empty($cinemaScreen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'cinema' => new CinemaScreenResource($cinemaScreen),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    //POST api/dashboard/cinema-screen/create
    public function store(CinemaScreenRequest $request)
    {
        try {
            $this->authorize('checkPermission', CinemaScreen::class);
            $credential = CinemaScreen::where('cinema_id', $request->cinema_id)
                ->where('screen_id', $request->screen_id)->first();
            if ($credential) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,);
            }
            $CinemaScreen = CinemaScreen::create($request->all());
            if (!$CinemaScreen) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            $data = [
                'cinemaScreen' => new CinemaScreenResource($CinemaScreen)
            ];
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //UPDATE api/dashboard/cinema-screen/update/{id}
    public function update(CinemaScreenRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', CinemaScreen::class);
            $CinemaScreen = CinemaScreen::find($id);
            empty($CinemaScreen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $credential = CinemaScreen::where('cinema_id', $request->cinema_id)
                ->where('screen_id', $request->screen_id)
                ->where('id', '!=', $id)
                ->first();
            if ($credential) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Quyền hạn của người dùng đã tồn tại.');
            }
            $screenUpdate = CinemaScreen::where('id', $id)->update([
                'cinema_id' => $request->cinema_id,
                'screen_id' => $request->screen_id
            ]);
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    //DELETE api/dashboard/cinema-screen/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', CinemaScreen::class);
            $CinemaScreen = CinemaScreen::where('deleted', 0)->find($id);
            empty($CinemaScreen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $CinemaScreen->deleted = 1;
            $CinemaScreen->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
