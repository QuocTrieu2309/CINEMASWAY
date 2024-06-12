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
            'cinema_screen_id'=>$this->cinema_screen_id,
            'seat_type_id'=>$this->seat_type_id,
            'cinema' => $this->cinemaScreen->cinema->name,
            'screen' => $this->cinemaScreen->screen->name,
            'seat_type'=> $this->seatType->name,
            'seat_number' => $this->seat_number,
            'status' => $this->status,
        ];
    }
}
