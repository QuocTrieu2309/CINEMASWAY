<?php

namespace App\Http\Resources\API\SeatType;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeatTypeResource extends JsonResource
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
            'cinema_screen_id'=>$this->cinema_screen_id,
            'cinema' => $this->cinema->name,
            'screen'=> $this->screen->name,
            'name' => $this->name,
            'price' => $this->price,
            'promotion_price' => $this->promotion_price,
        ];
    }
}
