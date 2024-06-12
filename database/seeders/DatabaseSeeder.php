<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

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

        \App\Models\Role::query()->create([
            'name'=> "Quản trị viên",
        ]);

        \App\Models\Permission::query()->create([
            'name'=> "Quản lý quyền hạn",
        ]);

        \App\Models\UserPermission::query()->create([
            'user_id'=> 1,
            'permission_id' => 1,
        ]);

        // \App\Models\Transaction::factory(10)->create();


        // $faker = Faker::create();
        // for ($i = 0; $i < 5; $i++) {
        //     DB::table('vouchers')->insert([
        //         'user_id' => 1,
        //         'code' => $faker->unique()->lexify('??????'),
        //         'pin' => $faker->numerify('######'),
        //         'type' => $faker->randomElement(['discount', 'gift']),
        //         'value' => $faker->randomFloat(2, 10, 100),
        //         'start_date' => $faker->dateTimeBetween('-1 month', '+1 month'),
        //         'end_date' => $faker->dateTimeBetween('+1 month', '+2 months'),
        //         'status' => $faker->randomElement(['active', 'expired', 'pending']),
        //         'description' => $faker->text,
        //         'created_by' => $faker->numberBetween(1, 10),
        //         'updated_by' => $faker->numberBetween(1, 10),
        //         'deleted' => 0,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }
    }
}
