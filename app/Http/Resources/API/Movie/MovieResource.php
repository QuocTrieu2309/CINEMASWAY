<?php

namespace App\Http\Resources\API\Movie;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
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
            'title' => $this->title,
            'genre' => $this->genre,
            'director' => $this->director,
            'actor' => $this->actor,
            'duration' => $this->duration,
            'release_date' => $this->release_date,
            'status' => $this->status,
            'is_early_showtime' => $this->is_early_showtime,
            'rated' => $this->rated,
            'like' => $this->like,
            'description' => $this->description,
            'image' => $this->image,
            'trailer' => $this->trailer,
            'end_date' => $this->end_date,
        ];
    }
}
