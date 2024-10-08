<?php

namespace App\Http\Controllers\API\Cinema;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Cinema\CinemaRequest;
use App\Http\Resources\API\Cinema\CinemaResource;
use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CinemaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * Display a listing of the resource.

     */
    //GET api/dashboard/cinema
    // Controller method
    public function index(Request $request)
    {
        try {
            $this->authorize('checkPermission', Cinema::class);
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Cinema::where('deleted', 0)
                ->withCount(['cinemaScreens as quantity' => function ($query) {
                    $query->where('deleted', 0);
                }])
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);

            $result = [
                'cinemas' => CinemaResource::collection($data),
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
    //POST api/dashboard/cinema/create
    public function store(CinemaRequest $request)
    {
        //
        try {
            $this->authorize('checkPermission', Cinema::class);

            $cinema = Cinema::create($request->all());
            if (!$cinema) {
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
    // GET /api/dashboard/cinema/{id}
    public function show($id)
    {
        try {
            $this->authorize('checkPermission', Cinema::class);
            $cinema = Cinema::where('id', $id)->where('deleted', 0)->first();
            empty($cinema) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
            $data = [
                'cinema' => new CinemaResource($cinema),
            ];
            return ApiResponse(true, $data, Response::HTTP_OK, messageResponseData());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    //UPDATE api/dashboard/cinema/update/{id}
    public function update(CinemaRequest $request, string $id)
    {
        try {
            $this->authorize('checkPermission', cinema::class);
            $cinema = Cinema::where('id', $id)->where('deleted', 0)->first();
            empty($cinema) && throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);

            $cinemaUpdated = cinema::where('id', $id)->update($request->all());
            return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    //DELETE api/dashboard/cinema/delete/{id}
    public function destroy(string $id)
{
    try {
        $this->authorize('delete', Cinema::class);
        DB::beginTransaction();
        $cinema = Cinema::where('id', $id)->where('deleted', 0)->first();
        if (empty($cinema)) {
            throw new \ErrorException(messageResponseNotFound(), Response::HTTP_BAD_REQUEST);
        }
        $now = now();
        $today = $now->toDateString();
        $hasActiveShowtimes = $cinema->cinemaScreens()
            ->whereHas('showtimes', function ($query) use ($now, $today) {
                $query->where(function ($subQuery) use ($now) {
                    $subQuery->where('show_time', '>', $now);
                })->orWhere(function ($subQuery) use ($today) {
                    $subQuery->where('show_date', '>=', $today);
                });
            })
            ->where('deleted', 0)
            ->exists();
        $hasSuccessfulBookings = $cinema->cinemaScreens()
            ->whereHas('showtimes', function ($query) {
                $query->whereHas('bookings', function ($query) {
                    $query->where('status', 'Payment successful');
                });
            })
            ->exists();

        if ($hasActiveShowtimes && $hasSuccessfulBookings) {
            throw new \ErrorException('Không thể xóa rạp chiếu có suất chiếu hoạt động và có vé đã đặt thành công', Response::HTTP_BAD_REQUEST);
        }
        $hasRelatedRecords = $cinema->cinemaScreens()->exists();
        if ($hasRelatedRecords) {
            $cinema->deleted = 1;
            $cinema->save();
        } else {
            $cinema->delete();
        }

        DB::commit();
        return ApiResponse(true, null, Response::HTTP_OK, messageResponseActionSuccess());
    } catch (\Exception $e) {
        DB::rollback();
        return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
    }
}

}
