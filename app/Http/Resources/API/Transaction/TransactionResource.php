<?php

namespace App\Http\Resources\API\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'booking_id' => $this->booking_id,
            'subtotal' => $this->subtotal,
            'payment_method' => $this->payment_method,
            'status' => $this->status
        ];
    }
}
