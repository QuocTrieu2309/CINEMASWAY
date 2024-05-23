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
            'cinema_screens_id' => $this->cinema_screens_id,
            'seat_type_id' => $this->seat_type_id,
            'seat_number' => $this->seat_number,
            'status' => $this->status,
        ];
    }
}
