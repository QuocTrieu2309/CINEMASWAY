<?php

namespace App\Http\Controllers\Api\CinemaScreen;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CinemaScreen\CinemaScreenRequest;
use App\Http\Resources\API\CinemaScreen\CinemaScreenResource;
use App\Models\CinemaScreens;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class CinemaScreenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/user-permission
    public function index(Request $request)
    {
        try {
            // $this->authorize('checkPermission',CinemaScreens::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = CinemaScreens::where('deleted',0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'data' => CinemaScreenResource::collection($data),
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

    //POST api/dashboard/user-permission/create
    public function store(CinemaScreenRequest $request)
    {
        try {
            // $this->authorize('checkPermission',CinemaScreens::class);
            $credential = CinemaScreens::where('cinema_id',$request->cinema_id)
                                        ->where('screen_id',$request->screen_id)->first();
            if($credential){
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, );
            }
            $cinemaScreens = CinemaScreens::create($request->all());
            if(!$cinemaScreens){
               return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,messageResponseActionFailed() );
            }
            $data = [
                'cinemaScreen' => new CinemaScreenResource($cinemaScreens)
            ];
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    public function update(CinemaScreenRequest $request, string $id){
        try {
            // $this->authorize('checkPermission',CinemaScreens::class);
            $cinemaScreens = CinemaScreens::find($id);
            empty($cinemaScreens) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $credential = CinemaScreens::where('cinema_id',$request->cinema_id)
                                        ->where('screen_id',$request->screen_id)
                                        ->where('id','!=',$id)
                                        ->first();
            if($credential){
                return ApiResponse(false, null, Response::HTTP_BAD_REQUEST,'Quyền hạn của người dùng đã tồn tại.' );
            }
            $screenUpdate = CinemaScreens::where('id', $id)->update([
                'cinema_id' => $request->cinema_id,
                'screen_id' =>$request->screen_id
            ]);
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    public function destroy(string $id){
        try {
            // $this->authorize('checkPermission',CinemaScreens::class);
            $CinemaScreens = CinemaScreens::where('deleted',0)->find($id);
            empty( $CinemaScreens) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $CinemaScreens->deleted = 1;
            $CinemaScreens->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
