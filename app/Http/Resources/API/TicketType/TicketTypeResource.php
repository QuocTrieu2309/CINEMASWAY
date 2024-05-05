<?php

namespace App\Http\Resources\API\TicketType;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketTypeResource extends JsonResource
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
            'seat_type_id' => $this->seat_type_id,
            'name' => $this->name,
            'price' => $this->price,
            'promotion_price' => $this->promotion_price,
            'deleted' => $this->deleted
        ];
    }
}
