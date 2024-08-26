<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id'=> rand(1,10),
            'subtotal' => 12345.12,
            'payment_method' => 'Thanh toán chuyển khoản',
            'status'=> 'Đã thanh toán',
        ];
    }
}
