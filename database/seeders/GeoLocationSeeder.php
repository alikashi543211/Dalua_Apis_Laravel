<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GeoLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('geo_locations')->truncate();
        // Mrine
        DB::table('geo_locations')->insert([
            [
                'name' => 'Komodo, Southern Hemisphere',
                'lat' => '-8.5825495',
                'lng' => '119.4811112',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'Raja Ampat',
                'lat' => '-1.0689761',
                'lng' => '130.7584744',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'Labuan Bajo',
                'lat' => '-8.4493076',
                'lng' => '119.8292079',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'Sorong',
                'lat' => '-0.8928211',
                'lng' => '131.2293763',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'Elphinstone Reef',
                'lat' => '25.309437',
                'lng' => '34.8603408',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'Tiputa pass',
                'lat' => '-14.971791',
                'lng' => '-147.6374902',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'Tubbataha Reef',
                'lat' => '8.850021',
                'lng' => '119.9245785',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'Jardines de la Reina',
                'lat' => '20.8333528',
                'lng' => '-78.9254215',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'Molokini Wall',
                'lat' => '20.6323253',
                'lng' => '-156.497065',
                'water_type' => 'Marine'
            ],
            [
                'name' => 'CAIRNS AUSTRALIA',
                'lat' => '-16.8801503',
                'lng' => '145.5768602',
                'water_type' => 'Marine'
            ],
        ]);
        // Fresh
        // DB::table('geo_locations')->insert([
        //     [
        //         'name' => 'Komodo, Southern Hemisphere',
        //         'lat' => '-8.5825495',
        //         'lng' => '119.4811112',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'Raja Ampat',
        //         'lat' => '-1.0689761',
        //         'lng' => '130.7584744',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'Labuan Bajo',
        //         'lat' => '-8.4493076',
        //         'lng' => '119.8292079',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'Sorong',
        //         'lat' => '-0.8928211',
        //         'lng' => '131.2293763',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'Elphinstone Reef',
        //         'lat' => '25.309437',
        //         'lng' => '34.8603408',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'Tiputa pass',
        //         'lat' => '-14.971791',
        //         'lng' => '-147.6374902',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'Tubbataha Reef',
        //         'lat' => '8.850021',
        //         'lng' => '119.9245785',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'Jardines de la Reina',
        //         'lat' => '20.8333528',
        //         'lng' => '-78.9254215',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'Molokini Wall',
        //         'lat' => '20.6323253',
        //         'lng' => '-156.497065',
        //         'water_type' => 'Fresh'
        //     ],
        //     [
        //         'name' => 'CAIRNS AUSTRALIA',
        //         'lat' => '-16.8801503',
        //         'lng' => '145.5768602',
        //         'water_type' => 'Fresh'
        //     ],
        // ]);

        DB::table('weather_configurations')->truncate();
        // Marine
        DB::table('weather_configurations')->insert([
            [
                'name' => WEATHER_SUNNY,
                'water_type' => 'Marine',
                'value_a' => 1,
                'value_b' => 1,
                'value_c' => 1,
            ],
            [
                'name' => WEATHER_PARTLY_CLOUDY,
                'water_type' => 'Marine',
                'value_a' => 0.95,
                'value_b' => 0.9,
                'value_c' => 0.9,
            ],
            [
                'name' => WEATHER_CLOUDY,
                'water_type' => 'Marine',
                'value_a' => 0.8,
                'value_b' => 0.7,
                'value_c' => 0.5,
            ],
            [
                'name' => WEATHER_RAIN,
                'water_type' => 'Marine',
                'value_a' => 0.65,
                'value_b' => 0.5,
                'value_c' => 0.25,
            ],
            [
                'name' => WEATHER_THUNDER_STORM,
                'water_type' => 'Marine',
                'value_a' => 0.4,
                'value_b' => 0.25,
                'value_c' => 0.15,
            ],
            [
                'name' => WEATHER_SUNNY,
                'water_type' => 'Fresh',
                'value_a' => 1,
                'value_b' => 1,
                'value_c' => 1,
            ],
            [
                'name' => WEATHER_PARTLY_CLOUDY,
                'water_type' => 'Fresh',
                'value_a' => 0.90,
                'value_b' => 0.90,
                'value_c' => 0.90,
            ],
            [
                'name' => WEATHER_CLOUDY,
                'water_type' => 'Fresh',
                'value_a' => 0.80,
                'value_b' => 0.80,
                'value_c' => 0.80,
            ],
            [
                'name' => WEATHER_RAIN,
                'water_type' => 'Fresh',
                'value_a' => 0.60,
                'value_b' => 0.60,
                'value_c' => 0.60,
            ],
            [
                'name' => WEATHER_THUNDER_STORM,
                'water_type' => 'Fresh',
                'value_a' => 0.50,
                'value_b' => 0.50,
                'value_c' => 0.50,
            ]
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
