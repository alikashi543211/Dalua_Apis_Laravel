<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_devices', function (Blueprint $table) {
            $table->id();
            $table->text('uuid')->nullable();
            $table->text('token')->nullable();
            $table->integer('type')->unsigned()->default(DEVICE_ANDROID);
            $table->bigInteger('user_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('notification_devices', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_devices');
    }
}
