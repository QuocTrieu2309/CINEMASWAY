<?php

namespace App\Http\Controllers\Api\Translation;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Translation\TranslationRequest;
use App\Http\Resources\API\Translation\TranslationResource;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class TranslationController extends Controller

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
        //GET api/dashboard/translation
        try{
              $this->authorize('checkPermission',Translation::class);
            $this->limit == $this->handleLimit($request->get('limit'),$this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Translation::where('deleted',0)->orderBy($this->sort, $this->order)->paginate($this->limit);
             $result = [
                'data' =>TranslationResource::collection($data),
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
    //POST api/dashboard/translation/create
    public function store(TranslationRequest $request)
    {
        //
        try{
              $this->authorize('checkPermission',Translation::class );

            $translation = Translation::create($request->all());
            if(!$translation){
                return ApiResponse(false,null,Response::HTTP_BAD_REQUEST,messageResponseActionFailed());
            }
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        }catch(\Exception $e){
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());

        }
    }

    /**
     * Display the specified resource.
     */
    // GET /api/translation/{id}
    public function show($id)
    {

        try{
              $this->authorize('checkPermission',Translation::class);
              $translation = Translation::where('id',$id)->where('deleted',0)->first();
            empty($translation) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'screen' => new  TranslationResource($translation),
            ];
            return ApiResponse(true, $data , Response::HTTP_OK,messageResponseActionSuccess());

        }catch(\Exception $e){
            return ApiResponse (false, null , Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    //UPDATE api/dashboard/translation/update/{id}
    public function update(TranslationRequest $request, string $id)
    {

        try{
          $this->authorize('checkPermission',Translation::class);
          $translation = Translation::where('id',$id)->where('deleted',0)->first();
            empty($translation) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $translationUpdated = Translation::where('id', $id)->update($request->all());
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch(\Exception $e){
            return ApiResponse (false, null , Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    //DELETE api/dashboard/translation/delete/{id}
    public function destroy(string $id)
    {
        try {
             $this->authorize('checkPermission',Translation::class);
             $translation = Translation::where('id',$id)->where('deleted',0)->first();
            empty($translation) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $translation->deleted = 1;
            $translation->save();
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

}

