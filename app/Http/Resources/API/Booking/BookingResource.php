<?php

namespace App\Http\Resources\API\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BookingResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}