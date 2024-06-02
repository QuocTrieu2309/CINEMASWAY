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
            'cinema' => $this->cinemaScreen->cinema->name,
            'screen' => $this->cinemaScreen->screen->name,
            'seat_number' => $this->seat->seat_number,
            'status' => $this->status,
        ];
    }
}
