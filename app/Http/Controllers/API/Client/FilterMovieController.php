<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FilterMovieController extends Controller
{
    public function filterMovie(Request $request)
    {
        $query = Showtime::query();

        if ($request->has('date')) {
            $query->where('show_date', $request->date);
        }

        if ($request->has('city')) {
            $query->whereHas('cinemaScreen.cinema', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }
        if ($request->has('genre')) {
            $query->whereHas('movie', function ($q) use ($request) {
                $q->where('genre', $request->genre);
            });
        }

        if ($request->has('experiences')) {
            $experiences = explode(':', $request->experiences);
            if (count($experiences) === 2) {
                $name = $experiences[0];
                $subtitle = $experiences[1];
                $query->whereHas('cinemaScreen.screen', function ($q) use ($name) {
                    $q->where('name', $name);
                });
                $query->where('subtitle', $subtitle);
            }
        }

        $result = $query->with('cinemaScreen.cinema','movie','cinemaScreen.screen')->get();
        if ($result->isEmpty()) {
            return ApiResponse(false, null, Response::HTTP_BAD_REQUEST, 'Không có bộ phim nào');
        }

        $list = $result->map(function ($movie) {
            return [
                'cinema_city' => $movie->cinemaScreen->cinema->city,
                'cinema_name' => $movie->cinemaScreen->cinema->name,
                'screen_name'=>$movie->cinemaScreen->screen->name,
                'subtitle' => $movie->subtitle,
                'movie_name' => $movie->movie->title,
                'movie_genre' => $movie->movie->genre,
                'movie_duration' => $movie->movie->duration,
                'show_date' => $movie->show_date,
                'show_time' => $movie->show_time,


            ];
        });

        return ApiResponse(true, $list, Response::HTTP_OK, messageResponseActionSuccess());
    }
}
