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
            'code' => $this->code,
            'ticket_code' =>$this->ticket_code,
            'user' => $this->user->full_name,
            'cinema' => $this->showtime->cinemaScreen->cinema->name,
            'screen' => $this->showtime->cinemaScreen->screen->name,
            'movie'=>$this->showtime->movie->title,
            'show_date'=> $this->showtime->show_date,
            'show_time'=> $this->showtime->show_time,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'status' => $this->status
        ];
    }
}
