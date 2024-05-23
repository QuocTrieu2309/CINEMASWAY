<?php

namespace App\Http\Resources\API\BookingService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingServiceResource extends JsonResource
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
            'booking_id' => $this->booking_id,
            'service_id' => $this->service_id,
            'subtotal' => $this->subtotal,
            'quantity' => $this->quantity,
        ];
    }
}
