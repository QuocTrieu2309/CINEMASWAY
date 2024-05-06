<?php

namespace App\Http\Controllers\API\Seat;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Seat\SeatRequest;
use App\Http\Resources\API\Seat\SeatResource;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class SeatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        try{
            // $this->authorize('chekPermission',Seat::class);
            $this->limit = $this->handleLimit($request->get('limit'),$this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Seat::where('deleted',0)->orderBy($this->sort, $this->order)->paginate($this->limit);
            $result = [
                'data' =>SeatResource::collection($data),
                'meta' => [
                    'total' => $data->total(),
                    'perPage' => $data->perPage(),
                    'currentPage' => $data->currentPage(),
                    'lastPage' => $data->lastPage(),
                ]
            ];
         return ApiResponse(true,$result, Response::HTTP_OK, messageResponseData());
        }catch(\Exception $e){
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SeatRequest $request)
    {
        //
        try{
            $this->authorize('checkPermission',Seat::class );

           $seat = Seat::create($request->all());
           if(!$seat){
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
    public function show(string $id)
    {
        //
        try {
            $this->authorize('checkPermission',Seat::class);
           $seat = Seat::where('id',$id)->where('deleted',0)->first();
           empty($seat) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
           $data = [
               'Seat' =>new SeatResource($seat),
           ];
           return ApiResponse(true,$data, Response::HTTP_OK, messageResponseData());
       } catch (\Exception $e) {
           return ApiResponse(false,null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
       }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SeatRequest $request, string $id)
    {
        //
        try {
            $this->authorize('checkPermission',Seat::class);
          $seat = Seat::where('id',$id)->where('deleted',0)->first();
          empty($seat) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

          $seatUpdated = Seat::where('id', $id)->update($request->all());
          return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
      } catch (\Exception $e) {
          return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
      }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {

            $this->authorize('checkPermission',Seat::class);
          $seat = Seat::where('id',$id)->where('deleted',0)->first();
          empty($seat) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
          $seat->deleted = 1;
          $seat->save();
          return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
      } catch (\Exception $e) {
          return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
      }
    }
}
