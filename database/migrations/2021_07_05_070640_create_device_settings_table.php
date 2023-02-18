<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('value_a')->unsigned()->default(0);
            $table->integer('value_b')->unsigned()->default(0);
            $table->integer('value_c')->unsigned()->default(0);
            $table->integer('master_control')->unsigned()->default(0);
            $table->bigInteger('device_id')->unsigned();
            $table->timestamps();
        });
        Schema::table('device_settings', function (Blueprint $table) {
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_settings');
    }
}
