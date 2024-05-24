<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'role_id' => $this->role_id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'password' => $this->password,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'avatar' => $this->avatar,
            'status' => $this->status,
            'email_verification_token' => $this->email_verification_token,
        ];
    }
}
