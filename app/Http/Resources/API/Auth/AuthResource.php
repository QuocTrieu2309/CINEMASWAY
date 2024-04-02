<?php

namespace App\Http\Resources\API\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'fullname' => $this->fullname,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'role_id' => $this->role_id,
            'status' => $this->status,
            'birth_date'=> $this->birth_date
        ];
    }
}
