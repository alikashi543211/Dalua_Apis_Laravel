<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IotDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('iot_devices')->truncate();
        DB::table('iot_device_configurations')->truncate();

        // Iot Devices
        DB::table('iot_devices')->insert([
            [
                'name' => 'SW2'
            ],
            [
                'name' => 'FRESH'
            ],
            // [
            //     'name' => 'SW3'
            // ],
            // [
            //     'name' => 'RGBW'
            // ],
            // [
            //     'name' => 'SA'
            // ]
        ]);

        // Iot Device Configurations
        DB::table('iot_device_configurations')->insert([
            // SW1 Version
            [
                'light' => 'L1',
                'channel' => 'A',
                'rgba' => 'rgba(136, 55, 249, 1)',
                'hex' => '#8837F9',
                'color_name' => 'marine_coral_c',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L2',
                'channel' => 'A',
                'rgba' => 'rgba(136, 55, 249, 1)',
                'hex' => '#8837F9',
                'color_name' => 'marine_coral_c',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L3',
                'channel' => 'A',
                'rgba' => 'rgba(136, 55, 249, 1)',
                'hex' => '#8837F9',
                'color_name' => 'marine_coral_c',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L4',
                'channel' => 'A',
                'rgba' => 'rgba(13, 48, 191, 1)',
                'hex' => '#0D30BF',
                'color_name' => 'marine_coral_c',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L5',
                'channel' => 'B',
                'rgba' => 'rgba(13, 48, 191, 1)',
                'hex' => '#0D30BF',
                'color_name' => 'marine_coral_b',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L8',
                'channel' => 'B',
                'rgba' => 'rgba(67, 169, 255, 1)',
                'hex' => '#43A9FF',
                'color_name' => 'marine_coral_b',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L6',
                'channel' => 'B',
                'rgba' => 'rgba(13, 48, 191, 1)',
                'hex' => '#0D30BF',
                'color_name' => 'marine_coral_b',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L7',
                'channel' => 'B',
                'rgba' => 'rgba(13, 48, 191, 1)',
                'hex' => '#0D30BF',
                'color_name' => 'marine_coral_b',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L9',
                'channel' => 'C',
                'rgba' => 'rgba(67, 169, 255, 1)',
                'hex' => '#65FF00',
                'color_name' => 'marine_coral_a',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L10',
                'channel' => 'C',
                'rgba' => 'rgba(241, 82, 11, 1)',
                'hex' => '#F1520B',
                'color_name' => 'marine_coral_a',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L11',
                'channel' => 'C',
                'rgba' => 'rgba(27, 70, 255, 1)',
                'hex' => ' #1B46FF',
                'color_name' => 'marine_coral_a',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L12',
                'channel' => 'C',
                'rgba' => 'rgba(254, 248, 47, 1)',
                'hex' => '#FEF82F',
                'color_name' => 'marine_coral_a',
                'iot_device_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],





            // Fresh Version
            [
                'light' => 'L1',
                'channel' => 'C',
                'rgba' => 'rgba(255, 255, 0, 1)',
                'hex' => '#ffff00',
                'color_name' => 'fresh_coral_c',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L2',
                'channel' => 'C',
                'rgba' => 'rgba(255, 255, 0, 1)',
                'hex' => '#ffff00',
                'color_name' => 'fresh_coral_c',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L3',
                'channel' => 'C',
                'rgba' => 'rgba(255, 255, 0, 1)',
                'hex' => '#ffff00',
                'color_name' => 'fresh_coral_c',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L4',
                'channel' => 'C',
                'rgba' => 'rgba(255, 255, 0, 1)',
                'hex' => '#ffff00',
                'color_name' => 'fresh_coral_c',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L5',
                'channel' => 'B',
                'rgba' => 'rgba(74, 255, 0, 1)',
                'hex' => '#4aff00',
                'color_name' => 'fresh_coral_b',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L6',
                'channel' => 'B',
                'rgba' => 'rgba(74, 255, 0, 1)',
                'hex' => '#4aff00',
                'color_name' => 'fresh_coral_b',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L7',
                'channel' => 'B',
                'rgba' => 'rgba(0, 255, 84, 1)',
                'hex' => '#00ff54',
                'color_name' => 'fresh_coral_b',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L8',
                'channel' => 'B',
                'rgba' => 'rgba(74, 255, 0, 1)',
                'hex' => '#4aff00',
                'color_name' => 'fresh_coral_b',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L9',
                'channel' => 'A',
                'rgba' => 'rgba(255, 79, 0,1)',
                'hex' => '#ff4f00',
                'color_name' => 'fresh_coral_a',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L11',
                'channel' => 'A',
                'rgba' => 'rgba(0, 70, 255,1)',
                'hex' => ' #0046ff',
                'color_name' => 'fresh_coral_a',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L12',
                'channel' => 'A',
                'rgba' => 'rgba(131, 0, 181, 1)',
                'hex' => '#8300b5',
                'color_name' => 'fresh_coral_a',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'light' => 'L10',
                'channel' => 'A',
                'rgba' => 'rgba(255,0,0,1)',
                'hex' => '#ff0000',
                'color_name' => 'fresh_coral_a',
                'iot_device_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

        ]);

        Schema::enableForeignKeyConstraints();
    }
}
