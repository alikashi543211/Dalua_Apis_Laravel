<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('users')->truncate();
        DB::table('users')->insert([
            [
                'id' => 1,
                'first_name' => 'Admin',
                'middle_name' => '',
                'last_name' => 'User',
                'username' => 'admin',
                'email' => 'admin@devstudio.us',
                'password' => Hash::make('admin123'),
                'email_verified_at' => Carbon::now(),
                'phone_no' => '+123456789',
                'role_id' => USER_ADMIN,
                'login_type' => LOGIN_EMAIL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
