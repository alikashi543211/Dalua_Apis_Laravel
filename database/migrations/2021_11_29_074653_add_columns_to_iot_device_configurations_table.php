<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToIotDeviceConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('iot_device_configurations', 'rgba')) {
            Schema::table('iot_device_configurations', function (Blueprint $table) {
                $table->dropColumn('value');
                $table->string('rgba')->nullable();
                $table->string('hex')->nullable();
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
        Schema::table('iot_device_configurations', function (Blueprint $table) {
            //
        });
    }
}
