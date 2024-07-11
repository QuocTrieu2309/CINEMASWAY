<?php

namespace App\Http\Controllers\API\Screen;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Screen\ScreenRequest;
use App\Http\Resources\API\Screen\ScreenResource;
use App\Models\Screen;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class ScreenController extends Controller

{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * Display a listing of the resource.
     */
    //GET api/dashboard/screen
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Screen::class);
            $this->limit == $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Screen::where('deleted', 0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'screens' => ScreenResource::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ]
            ];
            return ApiResponse(true, $result, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    //POST api/dashboard/screen/create
    public function store(ScreenRequest $request)
    {
        try {
            $this->authorize('checkPermission', Screen::class);
            $screen = Screen::create($request->all());
            if (!$screen) {
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    // GET /api/dashboard/screen/{id}
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', Screen::class);
            $screen = Screen::where('id', $id)->where('deleted', 0)->first();
            empty($screen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'screen' => new  ScreenResource($screen),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    //UPDATE api/dashboard/screen/update/{id}
    public function update(ScreenRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', Screen::class);
            $Screen = Screen::where('id', $id)->where('deleted', 0)->first();
            empty($Screen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $ScreenUpdated = Screen::where('id', $id)->update($request->all());
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    //DELETE api/dashboard/screen/delete/{id}
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', Screen::class);
            $Screen = Screen::where('id', $id)->where('deleted', 0)->first();
            empty($Screen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            // $Screen->deleted = 1;
            // $Screen->save();
            $hasRelatedRecords =  $Screen->cinemaScreens()->exists();
            if ($hasRelatedRecords) {
                $Screen->deleted = 1;
                $Screen->save();
            } else {
                $Screen->delete();
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
