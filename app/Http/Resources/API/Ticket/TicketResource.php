<?php

namespace App\Http\Resources\API\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'showtime_id' => $this->showtime_id,
            'seat_id' => $this->seat_id,
            'code' => $this->code,
            'status' => $this->status
        ];
    }
}