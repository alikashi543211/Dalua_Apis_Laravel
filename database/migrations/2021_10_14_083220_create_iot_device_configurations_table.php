<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIotDeviceConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iot_device_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('light');
            $table->enum('channel', ['A', 'B', 'C'])->default('A');
            $table->integer('value')->unsigned();
            $table->string('color_name')->nullable();
            $table->bigInteger('iot_device_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('iot_device_configurations', function (Blueprint $table) {
            $table->foreign('iot_device_id')->references('id')->on('iot_devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iot_device_configurations');
    }
}
