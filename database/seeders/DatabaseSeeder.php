<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'full_name' => 'Quốc Triệu',
            'role_id' => 1,
            'email' => 'cristianotrieulan@gmail.com',
            'phone' => '0364122323',
            'password' => Hash::make('23091999'),
            'gender' => \App\Models\User::GENDER_MALE,
            'birth_date' => now(),
            'avatar' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ0qj2hty4nA_9f4S4LeOJBNhHOUX6x99QdUfzWQC3QKLAEobv97QXhP3Oe6ocZhTnhlPU&usqp=CAU',
        ]);
    }
}
