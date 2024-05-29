<?php

namespace App\Http\Resources\API\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'user_id' => $this->user_id,
            'ticket_type_id' => $this->ticket_type_id,
            'showtime_id' => $this->showtime_id,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'status' => $this->status
        ];
    }
}
