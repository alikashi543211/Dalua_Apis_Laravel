<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('schedules')->insert([
            [
                'id' => 1,
                'name' => 'Mixed Reef',
                'geo_location' => false,
                'geo_location_id' => NULL,
                'public' => false,
                'user_id' => '1',
                'device_id' => NULL,
                'group_id' => NULL,
                'default' => true,
                'moonlight_enabled' => true,
                'mode' => SCHEDULE_ADVANCED,
                'slots' => json_encode([
                    [
                        "start_time" => "08:00:00",
                        "value_a" => "5",
                        "value_b" => "10",
                        "value_c" => "5",
                        "type" => TYPE_GRADUAL
                    ],
                    [
                        "start_time" => "10:00:00",
                        "value_a" => "25",
                        "value_b" => "55",
                        "value_c" => "30",
                        "type" => TYPE_GRADUAL
                    ],
                    [
                        "start_time" => "13:00:00",
                        "value_a" => "100",
                        "value_b" => "100",
                        "value_c" => "100",
                        "type" => TYPE_GRADUAL
                    ],
                    [
                        "start_time" => "15:00:00",
                        "value_a" => "80",
                        "value_b" => "100",
                        "value_c" => "65",
                        "type" => TYPE_GRADUAL
                    ],
                    [
                        "start_time" => "18:00:00",
                        "value_a" => "40",
                        "value_b" => "80",
                        "value_c" => "10",
                        "type" => TYPE_STEP
                    ],
                    [
                        "start_time" => "20:00:00",
                        "value_a" => "0",
                        "value_b" => "0",
                        "value_c" => "0",
                        "type" => TYPE_STEP
                    ]
                ]),
                'easy_slots' => "",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);


        Schema::enableForeignKeyConstraints();
    }
}
