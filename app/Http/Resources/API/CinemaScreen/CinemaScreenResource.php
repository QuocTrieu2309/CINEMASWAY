<?php

namespace App\Http\Resources\API\CinemaScreen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CinemaScreenResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->cinema->name,
            'city' => $this->cinema->city,
            'screen'=> $this->screen->name
        ];
    }
}
