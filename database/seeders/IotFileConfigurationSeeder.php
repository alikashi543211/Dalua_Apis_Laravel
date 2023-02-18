<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IotFileConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('iot_device_files')->truncate();
        DB::table('iot_device_files')->insert([
            [
                'name' => 'main.ino.esp32.bin',
                'location' => 'main.ino.esp32.bin',
                'version' => 1,
            ],
            [
                'name' => 'main.ino.esp32-4.bin',
                'location' => 'main.ino.esp32-4.bin',
                'version' => 4,
            ]
        ]);
    }
}
