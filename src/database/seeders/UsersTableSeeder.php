<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => '鈴木太郎',
            'email' => 'user1@gmail.com',
            'email_verified_at' => '2025-10-25 00:00:00',
            'created_at' => '2025-10-25 00:00:00',
            'password' => bcrypt('password'),
        ];
        DB::table('users')->insert($param);

        $param = [
            'name' => '鈴木花子',
            'email' => 'user2@gmail.com',
            'email_verified_at' => '2025-10-25 00:00:00',
            'created_at' => '2025-10-25 00:00:00',
            'password' => bcrypt('password'),
        ];
        DB::table('users')->insert($param);
    }
}
