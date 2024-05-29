<?php

namespace App\Http\Resources\API\Showtime;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ShowtimeResource extends JsonResource
{
    public function toArray($request): array
    {
        $showTime = Carbon::parse($this->show_time);
        $endTime = $showTime->copy()->addMinutes($this->movie->duration)->format('H:i:s');

        return [
            'id' => $this->id,
            'movie_id' => $this->movie_id,
            'cinema_screen_id' => $this->cinema_screen_id,
            'movie' => $this->movie->title,
            'cinema' => $this->cinemaScreen->cinema->name,
            'screen' => $this->cinemaScreen->screen->name,
            'subtitle' => $this->subtitle,
            'show_date' => $this->show_date,
            'show_time' => $this->show_time,
            'end_time' => $endTime,
            'status' => $this->status,
        ];
    }
}
