<?php

namespace App\Http\Resources\API\SeatMap;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeatMapResource extends JsonResource
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
            'screen' => $this->cinemaScreen->screen->name,
            'cinema' => $this->cinemaScreen->cinema->name,
            'seat_total' => $this->seat_total,
            'total_row' => $this->total_row,
            'total_column' => $this->total_column,
            'layout' => $this->layout
        ];
    }
}
