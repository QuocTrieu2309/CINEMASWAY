<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Showtime\ShowtimeResource;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class FilterController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->limit = $this->handleLimit($request->get('limit'), $this->limit);
            $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), $this->order);
            $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), $this->sort);
            $data = Showtime::where('deleted', 0)
                ->with('cinemaScreen.cinema')
                ->orderBy($this->sort, $this->order)
                ->paginate($this->limit);

            $result = [
                'showtimes' => ShowtimeResource::collection($data),
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
}
