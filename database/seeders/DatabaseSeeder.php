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

        // \App\Models\User::factory()->create([
        //     'full_name' => 'Quốc Triệu',
        //     'role_id' => 1,
        //     'email' => 'aa@gmail.com',
        //     'phone' => '0364122323',
        //     'password' => Hash::make('12345678'),
        //     'gender' => \App\Models\User::GENDER_MALE,
        //     'birth_date' => now(),
        //     'avatar' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ0qj2hty4nA_9f4S4LeOJBNhHOUX6x99QdUfzWQC3QKLAEobv97QXhP3Oe6ocZhTnhlPU&usqp=CAU',
        // ]);

        // \App\Models\Role::query()->create([
        //     'name'=> "Quản trị viên",
        // ]);

        // \App\Models\Permission::query()->create([
        //     'name'=> "Quản lý quyền hạn",
        // ]);

        // \App\Models\UserPermission::query()->create([
        //     'user_id'=> 3,
        //     'permission_id' => 1,
        // ]);

        // \App\Models\Movie::query()->create([
        //     'title'=> "King Kong 3",
        //     'genre'=> "Khoa hoc vien tuong",
        //     'director'=> "Trieu Cristiano",
        //     'actor'=>"Trieu handsome",
        //     'duration'=>"180",
        //     'release_date'=>"2024/05/10",
        //     'status'=>\App\Models\Movie::STATUS_COMING ,
        //     'rated'=> \App\Models\Movie::RATED_C18 ,
        // ]);

        // \App\Models\Translation::query()->create([
        //     'name'=> "Phu de tieng Viet",
        // ]);
    }
}
