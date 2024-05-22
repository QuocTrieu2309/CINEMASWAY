<?php

namespace App\Http\Resources\API\Seat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeatResource extends JsonResource
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
            'name' => $this->cinemaScreen->cinema->name,
            'city' => $this->cinemaScreen->cinema->city,
            'screen' => $this->cinemaScreen->screen->name,
            'seat_number' => $this->seat_number,
            'status' => $this->status,
        ];
    }
}
