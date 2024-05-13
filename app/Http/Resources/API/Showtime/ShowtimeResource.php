<?php

namespace App\Http\Resources\API\Showtime;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowtimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movie_id' => $this->movie_id,
            'cinema_screen_id' => $this->cinema_screen_id,
            'translation_id' => $this->translation_id,
            'show_date' => $this->show_date,
            'show_time' => $this->show_time,
            'status' => $this->status,
        ];
    }
}
