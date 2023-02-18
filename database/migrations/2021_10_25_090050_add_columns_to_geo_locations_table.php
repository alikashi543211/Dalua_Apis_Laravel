<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToGeoLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('geo_locations', 'lat')) {
            Schema::table('geo_locations', function (Blueprint $table) {
                $table->string('lat')->nullable();
                $table->string('lng')->nullable();
                $table->text('weather_data')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('geo_locations', function (Blueprint $table) {
            //
        });
    }
}
