<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ScheduleSeeder::class);
        // $this->call(GeoLocationSeeder::class);
        // $this->call(IotDeviceSeeder::class);
        // $this->call(IotFileConfigurationSeeder::class);
    }
}
