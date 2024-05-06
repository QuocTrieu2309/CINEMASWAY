<?php

namespace App\Http\Controllers\Api\Screen;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Screen\ScreenRequest;
use App\Http\Resources\API\Screen\ScreenResponse;
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
    public function index( Request $request)
    {
        //
        try{
            //   $this->authorize('checkPermission',Screen::class);
            $this->limit == $this->handleLimit($request->get('limit'),$this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Screen::where('deleted',0)->orderBy($this->sort, $this->order)->paginate($this->limit);
             $result = [
                'data' =>ScreenResponse::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ]
         ];
            return ApiResponse(true,$result, Response::HTTP_OK, messageResponseData());
        }catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());

    }
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScreenRequest $request)
    {
        //
        try{
            //  $this->authorize('checkPermission',Screen::class );

            $screen = Screen::create($request->all());
            if(!$screen){
                return ApiResponse(false,null,Response::HTTP_BAD_REQUEST,messageResponseActionFailed());
            }
            return ApiResponse(false,null,Response::HTTP_BAD_REQUEST,messageResponseActionSuccess());
        }catch(\Exception $e){
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());

        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        try{
            //  $this->authorize('checkPermission',Screen::class);
            $screen = Screen::where('id',$id)->where('deleted',0)->first();
            empty($screen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'screen' => new  ScreenResponse($screen),
            ];
            return ApiResponse(true, $data , Response::HTTP_OK,messageResponseActionSuccess());

        }catch(\Exception $e){
            return ApiResponse (false, null , Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScreenRequest $request, string $id)
    {

        try{//
        //  $this->authorize('checkPermission',Screen::class);
            $Screen = Screen::where('id',$id)->where('deleted',0)->first();
            empty($Screen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $ScreenUpdated = Screen::where('id', $id)->update($request->all());
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch(\Exception $e){
            return ApiResponse (false, null , Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            //  $this->authorize('checkPermission',Screen::class);
            $Screen = Screen::where('id',$id)->where('deleted',0)->first();
            empty($Screen) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $Screen->deleted = 1;
            $Screen->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

}

