<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_histories', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address')->nullable();
            $table->string('name')->nullable();
            $table->string('topic')->nullable();
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->timestamps();
        });

        Schema::table('device_histories', function (Blueprint $table) {
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
        Schema::dropIfExists('device_histories');
    }
}
