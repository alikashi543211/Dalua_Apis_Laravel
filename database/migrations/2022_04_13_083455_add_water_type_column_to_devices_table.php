<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWaterTypeColumnToDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('devices', 'water_type')) {

            Schema::table('devices', function (Blueprint $table) {
                $table->string('water_type')->nullable()->after('topic');
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
        if (Schema::hasColumn('devices', 'water_type')) {

            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('water_type');
            });

        }
    }
}
