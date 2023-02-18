<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypesToFloatInWeatherConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weather_configurations', function (Blueprint $table) {
            $table->float('value_a', 8, 2)->unsigned()->change();
            $table->float('value_b', 8, 2)->unsigned()->change();
            $table->float('value_c', 8, 2)->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('float_in_weather_configurations', function (Blueprint $table) {
            //
        });
    }
}
